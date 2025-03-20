<?php
require_once '../config/config.php';

// Fetch all sit-in records, including those with out_time
$query = "SELECT sit_in_id, idno, full_name, reason, lab, in_time, out_time, sit_date FROM sit_in ORDER BY sit_in_id ASC";
$result = $conn->query($query);

// Generate table rows
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr class='border-b hover:bg-bluegray/20'>
            <td class='p-3 text-center'>" . htmlspecialchars($row['idno']) . "</td>
            <td class='p-3 text-center'>" . htmlspecialchars($row['full_name']) . "</td>
            <td class='p-3 text-center'>" . htmlspecialchars($row['reason']) . "</td>
            <td class='p-3 text-center'>" . htmlspecialchars($row['lab']) . "</td>
            <td class='p-3 text-center'>" . htmlspecialchars($row['in_time']) . "</td>
            <td class='p-3 text-center'>" . ($row['out_time'] ? htmlspecialchars($row['out_time']) : '---') . "</td>
            <td class='p-3 text-center'>" . htmlspecialchars($row['sit_date']) . "</td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='7' class='text-center p-3 text-gray-500'>No sit-in records found</td></tr>";
}
?>
