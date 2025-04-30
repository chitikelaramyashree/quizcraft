<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
include('db.php');

try {
    $query = "SELECT 
        q.id,
        q.title,
        q.description,
        q.category,
        COUNT(qu.id) as question_count,
        DATE_FORMAT(q.created_at, '%b %e, %Y') as created_at
    FROM quizzes q
    LEFT JOIN questions qu ON q.id = qu.quiz_id
    GROUP BY q.id
    ORDER BY q.created_at DESC";

    $result = $conn->query($query);

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $quizzes = [];
    while ($row = $result->fetch_assoc()) {
        $quizzes[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'description' => $row['description'],
            'category' => $row['category'],
            'question_count' => $row['question_count'],
            'created_at' => $row['created_at']
        ];
    }

    echo json_encode(['success' => true, 'data' => $quizzes]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

$conn->close();
?>
