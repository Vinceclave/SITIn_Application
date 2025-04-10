<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if (isset($_GET['idno'])) {
    $idno = $_GET['idno'];
    
    // Fetch the most recent completed session for this student
    $query = "SELECT s.*, TIMESTAMPDIFF(MINUTE, s.in_time, s.out_time) as duration 
              FROM sit_in s 
              WHERE s.idno = ? AND s.out_time IS NOT NULL 
              ORDER BY s.sit_in_id DESC 
              LIMIT 1";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $idno);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $details = $result->fetch_assoc();
        
        // Format the times for better display
        $details['in_time'] = date('M d, Y h:i A', strtotime($details['in_time']));
        $details['out_time'] = date('M d, Y h:i A', strtotime($details['out_time']));
        
        // Format duration
        $hours = floor($details['duration'] / 60);
        $minutes = $details['duration'] % 60;
        $details['duration'] = sprintf("%d hours %d minutes", $hours, $minutes);
        
        echo json_encode([
            'success' => true,
            'details' => $details
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No completed session found for this student.'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Student ID not provided.'
    ]);
}
?> 