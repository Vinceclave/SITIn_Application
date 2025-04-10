<?php
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize input
    $sit_in_id = intval($_POST['sit_in_id']);
    $points = intval($_POST['points']);

    // Check if sit_in_id exists in the sit_in table
    $check_query = "SELECT idno FROM sit_in WHERE idno = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $sit_in_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Prepare the SQL statement to insert points
        $query = "INSERT INTO lab_points (sit_in_id, points, assigned_at) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($query);
        
        // Bind parameters and execute
        $stmt->bind_param("ii", $sit_in_id, $points);
        
        if ($stmt->execute()) {
            echo "Points saved successfully!";
            header("Location: sit_in_fetch.php");
            exit();
        } else {
            echo "Error saving points: " . $stmt->error;
        }
        
        $stmt->close();
    } else {
        echo "Invalid Sit In ID. Please check and try again.";
    }
    
    $check_stmt->close();
} else {
    // Handle the case where the script is accessed without a POST request
    echo "Invalid request method.";
}
?>
