<?php
require_once '../config/config.php'; // Adjust path as needed

// Retrieve search parameters (if any)
$lab = isset($_GET['lab']) ? trim($_GET['lab']) : '';
$idno = isset($_GET['idno']) ? trim($_GET['idno']) : '';

$query = "SELECT * FROM feedback WHERE 1";

// Apply filters if set
if (!empty($lab)) {
    $query .= " AND lab LIKE ?";
}
if (!empty($idno)) {
    $query .= " AND idno LIKE ?";
}

$stmt = $conn->prepare($query);
$bindParams = [];

if (!empty($lab)) {
    $bindParams[] = "%$lab%";
}
if (!empty($idno)) {
    $bindParams[] = "%$idno%";
}

if (!empty($bindParams)) {
    $stmt->bind_param(str_repeat("s", count($bindParams)), ...$bindParams);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td class='p-4 border-b'>" . htmlspecialchars($row['feedback_id']) . "</td>";
        echo "<td class='p-4 border-b'>" . htmlspecialchars($row['idno']) . "</td>";
        echo "<td class='p-4 border-b'>" . htmlspecialchars($row['lab']) . "</td>";
        echo "<td class='p-4 border-b'>" . htmlspecialchars($row['date']) . "</td>";
        echo "<td class='p-4 border-b'>" . htmlspecialchars($row['message']) . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='5' class='p-4 border-b text-center text-gray-500'>No feedback found.</td></tr>";
}

$stmt->close();
?>
