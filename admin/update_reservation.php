<?php
require_once '../config/config.php';

// Check if the data is posted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reservation_id = isset($_POST['reservation_id']) ? $_POST['reservation_id'] : '';
    $status = isset($_POST['status']) ? $_POST['status'] : '';

    // Validate inputs
    if (empty($reservation_id) || empty($status)) {
        echo 'Missing reservation_id or status';
        exit;
    }

    // Sanitize inputs
    $reservation_id = (int) $reservation_id;
    $status = mysqli_real_escape_string($conn, $status);

    // Update query
    $query = "UPDATE reservations SET status = ? WHERE reservation_id = ?";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        echo 'Error preparing statement';
        exit;
    }

    $stmt->bind_param('si', $status, $reservation_id);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'Error updating reservation status: ' . $stmt->error;
    }

    $stmt->close();
} else {
    echo 'Invalid request method';
}
?>
