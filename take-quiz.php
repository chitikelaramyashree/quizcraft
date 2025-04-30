<?php
// take-quiz.php
include 'db.php';
$quizId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch quiz info
$quizStmt = $conn->prepare("SELECT * FROM quizzes WHERE id = ?");
$quizStmt->bind_param("i", $quizId);
$quizStmt->execute();
$quizResult = $quizStmt->get_result();
$quiz = $quizResult->fetch_assoc();

if (!$quiz) {
    echo "Quiz not found.";
    exit;
}

// Fetch questions and answers
$questionsStmt = $conn->prepare("SELECT * FROM questions WHERE quiz_id = ?");
$questionsStmt->bind_param("i", $quizId);
$questionsStmt->execute();
$questionsResult = $questionsStmt->get_result();

$questions = [];
while ($q = $questionsResult->fetch_assoc()) {
    $answersStmt = $conn->prepare("SELECT id, text, is_correct FROM answers WHERE question_id = ?");
    $answersStmt->bind_param("i", $q['id']);
    $answersStmt->execute();
    $answersResult = $answersStmt->get_result();
    $answers = [];
    while ($a = $answersResult->fetch_assoc()) {
        $answers[] = $a;
    }
    $q['answers'] = $answers;
    $questions[] = $q;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($quiz['title']) ?> - Take Quiz</title>
  <style>
    body { font-family: Arial, sans-serif; background: #f9f9fb; margin:0; }
    .quiz-container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 12px #0002; padding: 32px; }
    .question-title { font-size: 1.2rem; margin-bottom: 16px; }
    .answers-list { list-style: none; padding: 0; }
    .answers-list li { margin-bottom: 12px; }
    .answer-btn { width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ddd; background: #f6f6fa; cursor: pointer; font-size: 1rem; text-align: left; }
    .answer-btn.correct { background: #a7f3d0; border-color: #34d399; color: #065f46; }
    .answer-btn.incorrect { background: #fecaca; border-color: #ef4444; color: #991b1b; }
    .score { font-size: 2rem; font-weight: bold; color: #715771; }
    .result-message { font-size: 1.2rem; margin: 20px 0; }
    .retry-btn, .exit-btn { padding: 10px 24px; border-radius: 6px; border: none; font-size: 1rem; margin: 10px 8px 0 0; cursor: pointer; }
    .retry-btn { background: #715771; color: #fff; }
    .exit-btn { background: #e5e7eb; color: #333; }
    .feedback { margin: 10px 0; font-weight: bold; }
    nav {
      background-color: #715771;
      color: white;
      padding: 16px;
      display: flex;
      align-items: center;
      font-size: 18px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    nav a {
      color: white;
      text-decoration: none;
    }
    .review-section { margin-top: 2rem; border-top: 2px solid #eee; padding-top: 1.5rem; }
    .review-question { margin-bottom: 1.5rem; padding: 1rem; background: #f8f9fa; border-radius: 8px; }
    .review-question.correct { border-left: 4px solid #34d399; }
    .review-question.incorrect { border-left: 4px solid #ef4444; }
    .answer-status { margin-top: 0.5rem; font-size: 0.95rem; }
    .user-answer { color: #ef4444; font-weight: 500; }
    .correct-answer { color: #10b981; font-weight: 500; }
  </style>
</head>
<body>
<nav>
    <a href="index.html">&lt; BACK TO HOMEPAGE</a>
</nav>
<div class="quiz-container">
  <h2><?= htmlspecialchars($quiz['title']) ?></h2>
  <p><?= htmlspecialchars($quiz['description']) ?></p>
  <div id="quiz-player"></div>
</div>
<script>
const questions = <?= json_encode($questions) ?>;
let current = 0;
let score = 0;
let selectedAnswers = [];

function renderQuestion() {
    const q = questions[current];
    let html = `<div class="question-title">Question ${current+1} of ${questions.length}:<br>${q.text}</div>`;
    html += '<ul class="answers-list">';
    q.answers.forEach((a, idx) => {
        html += `<li>
            <button class="answer-btn" data-idx="${idx}">${a.text}</button>
        </li>`;
    });
    html += '</ul>';
    html += `<div id="feedback" class="feedback"></div>`;
    document.getElementById('quiz-player').innerHTML = html;

    document.querySelectorAll('.answer-btn').forEach((btn, idx) => {
        btn.onclick = () => handleAnswer(idx);
    });
}

function handleAnswer(answerIdx) {
    const q = questions[current];
    const selected = q.answers[answerIdx];
    const correctIdx = q.answers.findIndex(a => a.is_correct == 1);

    // Store user's answer for review
    selectedAnswers[current] = {
        text: selected.text,
        isCorrect: selected.is_correct == 1
    };

    const feedbackDiv = document.getElementById('feedback');
    let feedback = '';
    if (selected.is_correct == 1) {
        feedback = '✅ Correct!';
        score++;
        document.querySelectorAll('.answer-btn')[answerIdx].classList.add('correct');
    } else {
        feedback = `❌ Incorrect! The correct answer is: <b>${q.answers[correctIdx].text}</b>`;
        document.querySelectorAll('.answer-btn')[answerIdx].classList.add('incorrect');
        document.querySelectorAll('.answer-btn')[correctIdx].classList.add('correct');
    }
    feedbackDiv.innerHTML = feedback;
    document.querySelectorAll('.answer-btn').forEach(btn => btn.disabled = true);

    setTimeout(() => {
        current++;
        if (current < questions.length) {
            renderQuestion();
        } else {
            renderResult();
        }
    }, 1500);
}

function renderResult() {
    let percent = Math.round((score / questions.length) * 100);
    let message = '';
    if (percent >= 90) message = "Amazing! You're a quiz master!";
    else if (percent >= 75) message = "Great job! You know your stuff!";
    else if (percent >= 50) message = "Good effort! Keep practicing!";
    else message = "Nice try! Want to give it another shot?";

    let resultsHtml = `
        <div class="score">Score: ${score}/${questions.length} (${percent}%)</div>
        <div class="result-message">${message}</div>
        <h3>Question Review:</h3>
    `;

    questions.forEach((q, index) => {
        const userAnswer = selectedAnswers[index];
        const correctAnswer = q.answers.find(a => a.is_correct == 1);

        resultsHtml += `
            <div class="review-question">
                <p><strong>Question ${index + 1}:</strong> ${q.text}</p>
                <div class="answer-status">
                    Your answer: <span class="${userAnswer?.isCorrect ? 'correct-answer' : 'user-answer'}">
                        ${userAnswer?.text || 'No answer'}
                    </span><br>
                    Correct answer: <span class="correct-answer">${correctAnswer.text}</span>
                </div>
            </div>
        `;
    });

    resultsHtml += `
        <button class="retry-btn" onclick="retryQuiz()">Retry</button>
        <button class="exit-btn" onclick="window.location.href='index.html'">Back to Quizzes</button>
    `;

    document.getElementById('quiz-player').innerHTML = resultsHtml;
}


function retryQuiz() {
    current = 0;
    score = 0;
    selectedAnswers = [];
    renderQuestion();
}

renderQuestion();
</script>
</body>
</html>
