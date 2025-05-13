<?php
require_once '../config/config.php';

// Check if user is logged in as admin
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Limit to the most recent feedback entries (default: 5)
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;

$query = "SELECT * FROM feedback ORDER BY date DESC LIMIT ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $limit);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $output = "";
    while ($row = $result->fetch_assoc()) {
        $output .= "<tr class='hover:bg-gray-50'>";
        $output .= "<td class='px-4 py-3'>" . htmlspecialchars($row['idno']) . "</td>";
        $output .= "<td class='px-4 py-3'>" . htmlspecialchars($row['lab']) . "</td>";
        
        // Format the date
        $date = new DateTime($row['date']);
        $formattedDate = $date->format('M d, Y - g:i A');
        $output .= "<td class='px-4 py-3'>" . $formattedDate . "</td>";
        
        // Limit message length for display
        $message = htmlspecialchars($row['message']);
        if (strlen($message) > 100) {
            $message = substr($message, 0, 100) . '...';
        }
        $output .= "<td class='px-4 py-3'>" . $message . "</td>";
        $output .= "</tr>";
    }
    echo $output;
} else {
    echo "<tr><td colspan='4' class='px-4 py-3 text-center text-gray-500'>No feedback available</td></tr>";
}

$stmt->close();
?>