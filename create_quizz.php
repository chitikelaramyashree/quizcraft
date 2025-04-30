<?php
header('Content-Type: application/json');
include('db.php');

$data = json_decode(file_get_contents('php://input'), true);

try {
    $conn->begin_transaction();

    // Insert quiz
    $stmt = $conn->prepare("INSERT INTO quizzes (title, description, category, question_count) VALUES (?, ?, ?, ?)");
    $question_count = count($data['questions']);
    $stmt->bind_param("sssi", $data['title'], $data['description'], $data['category'], $question_count);
    $stmt->execute();
    $quiz_id = $conn->insert_id;

    // Insert questions
    foreach ($data['questions'] as $question) {
        $stmt = $conn->prepare("INSERT INTO questions (quiz_id, text, difficulty) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $quiz_id, $question['text'], $question['difficulty']);
        $stmt->execute();
        $question_id = $conn->insert_id;

        // Insert answers
        foreach ($question['answers'] as $answer) {
            $stmt = $conn->prepare("INSERT INTO answers (question_id, text, is_correct) VALUES (?, ?, ?)");
            $is_correct = $answer['isCorrect'] ? 1 : 0;
            $stmt->bind_param("isi", $question_id, $answer['text'], $is_correct);
            $stmt->execute();
        }
    }

    $conn->commit();
    echo json_encode(['success' => true, 'quiz_id' => $quiz_id]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
