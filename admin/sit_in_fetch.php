<?php
require_once '../config/config.php';

$limit = 5; // Reduced limit to show more pages
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$search_name = isset($_GET['name']) ? trim($_GET['name']) : '';
$search_lab = isset($_GET['lab']) ? trim($_GET['lab']) : '';
$start_date = isset($_GET['startDate']) && !empty($_GET['startDate']) ? $_GET['startDate'] : null;
$end_date = isset($_GET['endDate']) && !empty($_GET['endDate']) ? $_GET['endDate'] : null;

// Build the WHERE clause
$where_conditions = ["s.full_name LIKE ?", "s.lab LIKE ?"];
$params = ["%$search_name%", "%$search_lab%"];
$types = "ss";

// Add date range conditions if provided
if ($start_date) {
    $where_conditions[] = "s.sit_date >= ?";
    $params[] = $start_date;
    $types .= "s";
}

if ($end_date) {
    $where_conditions[] = "s.sit_date <= ?";
    $params[] = $end_date;
    $types .= "s";
}

$where_clause = implode(" AND ", $where_conditions);

// Get total count for pagination
$count_query = "
    SELECT COUNT(*) as total 
    FROM sit_in s
    WHERE $where_clause
";

$count_params = $params;
$count_stmt = $conn->prepare($count_query);
$count_stmt->bind_param(substr($types, 0, strlen($types)), ...$count_params);
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$count_row = $count_result->fetch_assoc();
$total_records = $count_row['total'];
$total_pages = ceil($total_records / $limit);

// Main query for records
$query = "
    SELECT s.idno, s.full_name, s.lab, s.reason, 
           CASE 
               WHEN s.out_time IS NOT NULL THEN 'Inactive' 
               ELSE 'Active' 
           END AS status, 
           ss.session,
           lp.id AS points_id,
           lp.points,
           s.sit_in_id,
           s.in_time,
           s.out_time,
           s.sit_date
    FROM sit_in s
    LEFT JOIN student_session ss ON s.idno = ss.idno 
    LEFT JOIN lab_points lp ON s.sit_in_id = lp.sit_in_id
    WHERE $where_clause
    ORDER BY s.sit_date DESC, s.in_time DESC
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($query);
$params[] = $limit;
$params[] = $offset;
$types .= "ii";
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Start an output buffer to capture the HTML output
ob_start();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Format date and time
        $sitDate = $row['sit_date'] ? date('M d, Y', strtotime($row['sit_date'])) : 'N/A';
        $inTime = $row['in_time'] ? date('h:i A', strtotime($row['in_time'])) : 'N/A';
        $outTime = $row['out_time'] ? date('h:i A', strtotime($row['out_time'])) : 'N/A';
        
        // Calculate duration if both in and out times exist
        $duration = 'N/A';
        if ($row['in_time'] && $row['out_time']) {
            $in = new DateTime($row['in_time']);
            $out = new DateTime($row['out_time']);
            $diff = $in->diff($out);
            $duration = $diff->format('%H:%I');
        }
        
        echo "<tr class='border-b hover:bg-bluegray/20' data-id='{$row['idno']}'" . 
             (isset($row['points_id']) ? " data-points-id='{$row['points_id']}'" : "") . ">
            <td class='p-3 text-center'>" . htmlspecialchars($row['idno']) . "</td>
            <td class='p-3 text-center'>" . htmlspecialchars($row['full_name']) . "</td>
            <td class='p-3 text-center'>" . htmlspecialchars($row['lab']) . "</td>
            <td class='p-3 text-center'>" . htmlspecialchars($row['reason']) . "</td>
            <td class='p-3 text-center'>
                <div class='flex flex-col items-center'>
                    <span class='font-semibold " . ($row['status'] === 'Active' ? 'text-green-600' : 'text-gray-600') . "'>" . 
                        htmlspecialchars($row['status']) . 
                    "</span>
                    <span class='text-xs text-gray-500'>" . $sitDate . "</span>
                </div>
            </td>
            <td class='p-3 text-center'>
                <div class='flex flex-col items-center'>
                    <span class='font-medium'>" . htmlspecialchars($row['session'] ?? 'N/A') . "</span>
                    <span class='text-xs text-gray-500'>" . $inTime . " - " . $outTime . "</span>
                    <span class='text-xs text-gray-500'>Duration: " . $duration . "</span>
                    <button class='reset-session-btn mt-1 text-xs bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 transition-colors' data-id='{$row['idno']}' data-name='" . htmlspecialchars($row['full_name']) . "'>
                        <i class='fas fa-sync-alt mr-1'></i>Reset Sessions
                    </button>
                </div>
            </td>
            <td class='p-3 text-center'>" . 
                ($row['status'] === 'Active' 
                    ? "<button class='end-btn bg-red-500 text-white px-3 py-2 rounded-md hover:bg-red-600 transition-colors' data-id='{$row['idno']}'>
                        <i class='fas fa-stop-circle mr-1'></i>End
                       </button>" 
                    : "<div class='flex items-center justify-center space-x-2'>
                          <span class='px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-medium'>
                            <i class='fas fa-check-circle mr-1'></i>Completed
                          </span>
                          " . 
                          (isset($row['points_id']) 
                            ? "<span class='px-3 py-2 bg-gray-400 text-white rounded-md cursor-not-allowed'>
                                <i class='fas fa-award mr-1'></i>+" . htmlspecialchars($row['points'] ?? '3') . " Points Given
                               </span>"
                            : (intval($row['session']) >= 30
                                ? "<span class='px-3 py-2 bg-gray-400 text-white rounded-md cursor-not-allowed'>
                                    <i class='fas fa-ban mr-1'></i>Max Sessions Reached
                                   </span>"
                                : "<div class='flex items-center space-x-2'>
                                    <select class='points-select bg-white border border-gray-300 rounded-md px-2 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500'>
                                      <option value='1'>1 Point</option>
                                      <option value='2'>2 Points</option>
                                      <option value='3'>3 Points</option>
                                    </select>
                                    <button class='points-btn bg-purple-500 text-white px-3 py-2 rounded-md hover:bg-purple-600 transition-colors' 
                                            data-id='{$row['idno']}' data-sit-in-id='" . htmlspecialchars($row['sit_in_id']) . "'>
                                      <i class='fas fa-award mr-1'></i>Give Points
                                    </button>
                                   </div>")) .
                       "</div>") . 
            "</td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='7' class='text-center p-3 text-gray-500'>No records found</td></tr>";
}

// Get the HTML content
$tableHtml = ob_get_clean();

// Create and return a JSON response with both the table HTML and pagination data
$response = [
    'tableHtml' => $tableHtml,
    'pagination' => [
        'currentPage' => $page,
        'totalPages' => $total_pages,
        'totalRecords' => $total_records
    ]
];

// Set the content type to JSON
header('Content-Type: application/json');
echo json_encode($response);
?>