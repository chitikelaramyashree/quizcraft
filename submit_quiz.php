<?php
session_start();
header('Content-Type: application/json');
include('db.php');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$quizId = $data['quizId'];
$score = $data['score'];

// Validate quiz exists
$quizCheck = $conn->prepare("SELECT id FROM quizzes WHERE id = ?");
$quizCheck->bind_param("i", $quizId);
$quizCheck->execute();
if (!$quizCheck->get_result()->num_rows) {
    echo json_encode(['success' => false, 'error' => 'Invalid quiz']);
    exit;
}

// Update leaderboard
$stmt = $conn->prepare("
    INSERT INTO leaderboard (user_id, quiz_id, score)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE
    score = GREATEST(score, VALUES(score))
");
$stmt->bind_param("iii", $_SESSION['user_id'], $quizId, $score);
$stmt->execute();

echo json_encode(['success' => true]);
?>
