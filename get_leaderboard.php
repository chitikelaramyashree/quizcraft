<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
include('db.php');

try {
    // Use simple aggregation without window functions
    $query = "
        SELECT 
            u.id,
            u.name,
            COALESCE(SUM(l.score), 0) AS score,
            COUNT(DISTINCT l.quiz_id) AS quizzes
        FROM users u
        LEFT JOIN leaderboard l ON u.id = l.user_id
        GROUP BY u.id
        ORDER BY score DESC, quizzes DESC
        LIMIT 5
    ";

    $result = $conn->query($query);
    $leaderboard = [];
    while ($row = $result->fetch_assoc()) {
        $leaderboard[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'score' => (int)$row['score'],
            'quizzes' => (int)$row['quizzes']
        ];
    }
    echo json_encode(['success' => true, 'data' => $leaderboard]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
$conn->close();
?>
