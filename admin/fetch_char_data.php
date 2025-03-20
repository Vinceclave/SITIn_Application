<?php
require '../config/config.php';

$query = $conn->query("SELECT DATE(usage_date) as date, COUNT(*) as sessions FROM student_sessions GROUP BY DATE(usage_date) ORDER BY date DESC LIMIT 7");

$data = ['labels' => [], 'sessions' => []];

while ($row = $query->fetch_assoc()) {
    $data['labels'][] = $row['date'];
    $data['sessions'][] = $row['sessions'];
}

$conn->close();
echo json_encode($data);
?>
