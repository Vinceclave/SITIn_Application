<?php
require_once '../config/config.php';

$limit = 5;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$search_name = isset($_GET['name']) ? trim($_GET['name']) : '';
$search_lab = isset($_GET['lab']) ? trim($_GET['lab']) : '';

$query = "
    SELECT s.idno, s.full_name, s.lab, s.reason, 
           CASE 
               WHEN s.out_time IS NOT NULL THEN 'Inactive' 
               ELSE 'Active' 
           END AS status, 
           ss.session 
    FROM sit_in s
    LEFT JOIN student_session ss ON s.idno = ss.idno 
    WHERE s.full_name LIKE ? AND s.lab LIKE ? 
    ORDER BY s.idno ASC 
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($query);
$search_name = "%$search_name%";
$search_lab = "%$search_lab%";
$stmt->bind_param("ssii", $search_name, $search_lab, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr class='border-b hover:bg-bluegray/20' data-id='{$row['idno']}'>
            <td class='p-3 text-center'>" . htmlspecialchars($row['idno']) . "</td>
            <td class='p-3 text-center'>" . htmlspecialchars($row['full_name']) . "</td>
            <td class='p-3 text-center'>" . htmlspecialchars($row['lab']) . "</td>
            <td class='p-3 text-center'>" . htmlspecialchars($row['reason']) . "</td>
            <td class='p-3 text-center font-semibold'>" . htmlspecialchars($row['status']) . "</td>
            <td class='p-3 text-center'>" . htmlspecialchars($row['session'] ?? 'N/A') . "</td>
            <td class='p-3 text-center'>" . 
                ($row['status'] === 'Active' ? "<button class='end-btn bg-red-500 text-white px-3 py-2 rounded-md' data-id='{$row['idno']}'>End</button>" : "Completed") . 
            "</td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='7' class='text-center p-3 text-gray-500'>No records found</td></tr>";
}
?>
