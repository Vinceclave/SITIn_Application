<?php
// filepath: d:\Xampp\htdocs\SITIn_Application\student\get_available_pcs.php
require_once '../config/config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Set content type to JSON
header('Content-Type: application/json');

// Check if lab_name, date, and time slot are provided
if (!isset($_GET['lab_name']) || empty($_GET['lab_name']) ||
    !isset($_GET['date']) || empty($_GET['date']) ||
    !isset($_GET['time_slot']) || empty($_GET['time_slot'])) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$lab_name = filter_var($_GET['lab_name'], FILTER_SANITIZE_STRING);
$date = filter_var($_GET['date'], FILTER_SANITIZE_STRING);
$time_slot = filter_var($_GET['time_slot'], FILTER_SANITIZE_STRING);

// Validate date format (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format']);
    exit;
}

// Get lab ID
$labQuery = "SELECT lab_id, total_pcs FROM labs WHERE lab_name = ?";
$labStmt = $conn->prepare($labQuery);
$labStmt->bind_param("s", $lab_name);
$labStmt->execute();
$labResult = $labStmt->get_result();

if ($labResult->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Lab not found']);
    exit;
}

$labData = $labResult->fetch_assoc();
$lab_id = $labData['lab_id'];
$total_pcs = $labData['total_pcs'];

// Get all PCs in this lab
$pcsQuery = "SELECT pc_id, pc_number, status FROM pcs WHERE lab_id = ? ORDER BY pc_number";
$pcsStmt = $conn->prepare($pcsQuery);
$pcsStmt->bind_param("i", $lab_id);
$pcsStmt->execute();
$pcsResult = $pcsStmt->get_result();

$pcs = [];
while ($pc = $pcsResult->fetch_assoc()) {
    $pcs[$pc['pc_number']] = [
        'pc_id' => $pc['pc_id'],
        'pc_number' => $pc['pc_number'],
        'status' => $pc['status'],
        'available' => ($pc['status'] === 'available') ? true : false
    ];
}

// Get reservations for this lab, date, and time slot
$reservationsQuery = "SELECT pc_number, status FROM reservations WHERE lab_name = ? AND reservation_date = ? AND time_slot = ?";
$reservationsStmt = $conn->prepare($reservationsQuery);
$reservationsStmt->bind_param("sss", $lab_name, $date, $time_slot);
$reservationsStmt->execute();
$reservationsResult = $reservationsStmt->get_result();

// Mark reserved PCs as unavailable
while ($reservation = $reservationsResult->fetch_assoc()) {
    $pc_number = $reservation['pc_number'];
    if (isset($pcs[$pc_number])) {
        if ($reservation['status'] === 'approved' || $reservation['status'] === 'pending') {
            $pcs[$pc_number]['available'] = false;
            $pcs[$pc_number]['reserved'] = true;
        }
    }
}

// Get active sit-in sessions for this lab
$sitInQuery = "SELECT pc_number FROM sit_in WHERE lab = ? AND status = 1 AND out_time IS NULL";
$sitInStmt = $conn->prepare($sitInQuery);
$sitInStmt->bind_param("s", $lab_name);
$sitInStmt->execute();
$sitInResult = $sitInStmt->get_result();

// Mark PCs with active sessions as unavailable
while ($session = $sitInResult->fetch_assoc()) {
    $pc_number = $session['pc_number'];
    if (isset($pcs[$pc_number])) {
        $pcs[$pc_number]['available'] = false;
        $pcs[$pc_number]['in_use'] = true;
    }
}

// Convert associative array to indexed array
$pcsArray = array_values($pcs);

echo json_encode([
    'success' => true,
    'lab_name' => $lab_name,
    'date' => $date,
    'time_slot' => $time_slot,
    'pcs' => $pcsArray
]);

$conn->close();
?>
