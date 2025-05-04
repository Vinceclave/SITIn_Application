<?php
session_start();
require_once '../config/config.php';
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get parameters
        $lab_name = isset($_POST['lab_name']) ? $_POST['lab_name'] : '';
        $reservation_date = isset($_POST['reservation_date']) ? $_POST['reservation_date'] : '';
        $time_slot = isset($_POST['time_slot']) ? $_POST['time_slot'] : '';

        // Validate required parameters
        if (empty($lab_name) || empty($reservation_date) || empty($time_slot)) {
            throw new Exception("Missing required parameters");
        }

        // Sanitize inputs to prevent SQL injection
        $lab_name = htmlspecialchars(strip_tags($lab_name));
        $reservation_date = htmlspecialchars(strip_tags($reservation_date));
        $time_slot = htmlspecialchars(strip_tags($time_slot));

        // Get list of PCs already reserved for this lab, date, and time slot
        $query = "SELECT pc_number FROM reservations 
                  WHERE lab_name = ? 
                  AND reservation_date = ? 
                  AND time_slot = ? 
                  AND status IN ('pending', 'approved')";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("sss", $lab_name, $reservation_date, $time_slot);
        $stmt->execute();
        $result = $stmt->get_result();

        // Build array of reserved PC numbers
        $reservedPCs = [];
        while ($row = $result->fetch_assoc()) {
            $reservedPCs[] = (int)$row['pc_number'];
        }

        // Return JSON array of reserved PCs
        echo json_encode($reservedPCs);
    } else {
        // Invalid request method
        throw new Exception("Invalid request method");
    }
} catch (Exception $e) {
    http_response_code(500); // Set HTTP status code to 500 (Internal Server Error)
    echo json_encode(['error' => $e->getMessage()]);
}

?> 