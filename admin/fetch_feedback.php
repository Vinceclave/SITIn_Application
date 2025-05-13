<?php
require_once '../config/config.php'; // Adjust path as needed

// Retrieve search parameters
$lab = isset($_GET['lab']) ? trim($_GET['lab']) : '';
$idno = isset($_GET['idno']) ? trim($_GET['idno']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$offset = ($page - 1) * $limit;

// Base query for filtering
$baseQuery = "SELECT * FROM feedback WHERE 1";
$countQuery = "SELECT COUNT(*) as total FROM feedback WHERE 1";

// Apply filters if set
if (!empty($lab)) {
    $baseQuery .= " AND lab LIKE ?";
    $countQuery .= " AND lab LIKE ?";
}
if (!empty($idno)) {
    $baseQuery .= " AND idno LIKE ?";
    $countQuery .= " AND idno LIKE ?";
}

// Add sorting and pagination
$baseQuery .= " ORDER BY date DESC LIMIT ?, ?";

// Prepare and bind for count query
$countStmt = $conn->prepare($countQuery);
$bindParams = [];

if (!empty($lab)) {
    $bindParams[] = "%$lab%";
}
if (!empty($idno)) {
    $bindParams[] = "%$idno%";
}

if (!empty($bindParams)) {
    $types = str_repeat("s", count($bindParams));
    $countStmt->bind_param($types, ...$bindParams);
}

$countStmt->execute();
$countResult = $countStmt->get_result();
$countRow = $countResult->fetch_assoc();
$totalRecords = $countRow['total'];
$totalPages = ceil($totalRecords / $limit);
$countStmt->close();

// Prepare and execute the main query
$stmt = $conn->prepare($baseQuery);
$mainBindParams = $bindParams;
$mainBindParams[] = $offset;
$mainBindParams[] = $limit;

if (!empty($mainBindParams)) {
    $types = str_repeat("s", count($bindParams)) . "ii";
    $stmt->bind_param($types, ...$mainBindParams);
}

$stmt->execute();
$result = $stmt->get_result();

// Prepare the response
$response = [];

// Add pagination info
$response['pagination'] = [
    'page' => $page,
    'limit' => $limit,
    'total' => $totalRecords,
    'total_pages' => $totalPages
];

// Add table rows HTML
$tableHTML = '';

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tableHTML .= "<tr class='hover:bg-gray-50'>";
        $tableHTML .= "<td class='px-6 py-4 text-sm text-gray-500'>" . htmlspecialchars($row['feedback_id']) . "</td>";
        $tableHTML .= "<td class='px-6 py-4 text-sm text-gray-700'>" . htmlspecialchars($row['idno']) . "</td>";
        $tableHTML .= "<td class='px-6 py-4 text-sm text-gray-700'>" . htmlspecialchars($row['lab']) . "</td>";
        
        // Format the date
        $date = new DateTime($row['date']);
        $formattedDate = $date->format('M d, Y - g:i A');
        $tableHTML .= "<td class='px-6 py-4 text-sm text-gray-500'>" . $formattedDate . "</td>";
        
        $tableHTML .= "<td class='px-6 py-4 text-sm text-gray-700'>" . htmlspecialchars($row['message']) . "</td>";
        $tableHTML .= "</tr>";
    }
} else {
    $tableHTML = "<tr><td colspan='5' class='px-6 py-4 text-center text-gray-500'>No feedback found matching your criteria.</td></tr>";
}

// Check if this is an AJAX request or direct access
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

if ($isAjax) {
    $response['html'] = $tableHTML;
    echo json_encode($response);
} else {
    echo $tableHTML;
}

$stmt->close();
?>
