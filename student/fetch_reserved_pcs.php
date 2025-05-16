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

        // Get lab ID
        $labQuery = "SELECT lab_id FROM labs WHERE lab_name = ?";
        $labStmt = $conn->prepare($labQuery);
        $labStmt->bind_param("s", $lab_name);
        $labStmt->execute();
        $labResult = $labStmt->get_result();
        
        if ($labResult->num_rows === 0) {
            throw new Exception("Lab not found");
        }
        
        $lab_id = $labResult->fetch_assoc()['lab_id'];
        
        // Get unavailable PCs (based on PC status)
        $unavailablePCsQuery = "SELECT pc_number FROM pcs 
                               WHERE lab_id = ? AND status != 'available'";
        $unavailablePCsStmt = $conn->prepare($unavailablePCsQuery);
        $unavailablePCsStmt->bind_param("i", $lab_id);
        $unavailablePCsStmt->execute();
        $unavailablePCsResult = $unavailablePCsStmt->get_result();
        
        // Build array of unavailable PC numbers from PC status
        $unavailablePCs = [];
        while ($row = $unavailablePCsResult->fetch_assoc()) {
            $unavailablePCs[] = (int)$row['pc_number'];
        }

        // Get list of PCs already reserved for this lab, date, and time slot
        $reservedPCsQuery = "SELECT pc_number FROM reservations 
                           WHERE lab_name = ? 
                           AND reservation_date = ? 
                           AND time_slot = ? 
                           AND status IN ('pending', 'approved')";
        $reservedPCsStmt = $conn->prepare($reservedPCsQuery);
        $reservedPCsStmt->bind_param("sss", $lab_name, $reservation_date, $time_slot);
        $reservedPCsStmt->execute();
        $reservedPCsResult = $reservedPCsStmt->get_result();

        // Build array of reserved PC numbers
        $reservedPCs = [];
        while ($row = $reservedPCsResult->fetch_assoc()) {
            $reservedPCs[] = (int)$row['pc_number'];
        }
        
        // Get list of PCs currently in use (active sit-in sessions)
        $inUsePCsQuery = "SELECT pc_number FROM sit_in 
                         WHERE lab = ? 
                         AND status = 1 
                         AND out_time IS NULL";
        $inUsePCsStmt = $conn->prepare($inUsePCsQuery);
        $inUsePCsStmt->bind_param("s", $lab_name);
        $inUsePCsStmt->execute();
        $inUsePCsResult = $inUsePCsStmt->get_result();
        
        // Build array of in-use PC numbers
        $inUsePCs = [];
        while ($row = $inUsePCsResult->fetch_assoc()) {
            $inUsePCs[] = (int)$row['pc_number'];
        }
        
        // Combine all unavailable PCs
        $allUnavailablePCs = array_unique(array_merge($unavailablePCs, $reservedPCs, $inUsePCs));
        
        // Return JSON array of unavailable PCs
        echo json_encode($allUnavailablePCs);
    } else {
        // Invalid request method
        throw new Exception("Invalid request method");
    }
} catch (Exception $e) {
    http_response_code(500); // Set HTTP status code to 500 (Internal Server Error)
    echo json_encode(['error' => $e->getMessage()]);
}

?> 