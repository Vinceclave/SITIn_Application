<?php
require_once '../config/config.php';

header('Content-Type: application/json'); // Ensure the response is JSON

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['idno'])) {
    $idno = $_POST['idno'];

    // Fetch current inTime and outTime
    $query = "SELECT in_time, out_time FROM sit_in WHERE idno = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $idno);
    $stmt->execute();
    $result = $stmt->get_result();
    $sit_in_record = $result->fetch_assoc();

    if ($sit_in_record) {
        $inTime = $sit_in_record['in_time'];
        $outTime = $sit_in_record['out_time'];

        if ($inTime && !$outTime) {
            // Set out_time to current time
            $query = "UPDATE sit_in SET out_time = NOW() WHERE idno = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $idno);
            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Sit-in ended successfully."]);
            } else {
                echo json_encode(["success" => false, "message" => "Error updating out_time."]);
            }
        } elseif (!$inTime && !$outTime) {
            // Set both in_time and out_time to current time
            $query = "UPDATE sit_in SET in_time = NOW(), out_time = NOW() WHERE idno = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $idno);
            if ($stmt->execute()) {
                echo json_encode(["success" => true, "message" => "Sit-in started and ended at the same time."]);
            } else {
                echo json_encode(["success" => false, "message" => "Error setting in_time and out_time."]);
            }
        }
    } else {
        echo json_encode(["success" => false, "message" => "Sit-in record not found."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
}
?>
