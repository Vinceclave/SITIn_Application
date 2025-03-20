<?php
require_once '../config/config.php';

$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;

$query = "SELECT * FROM announcements ORDER BY date DESC LIMIT ?, ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $offset, $limit);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    echo '<li class="border-b border-gray-300 pb-3">';
    echo '<p class="text-sm text-gray-600 font-semibold">Posted by: ' . htmlspecialchars($row['admin_name']) . '</p>';
    echo '<p class="text-xs text-gray-500">' . date('F j, Y, g:i a', strtotime($row['date'])) . '</p>';
    echo '<p class="mt-2 text-gray-800">' . nl2br(htmlspecialchars($row['message'])) . '</p>';
    echo '</li>';
}

$stmt->close();
?>
