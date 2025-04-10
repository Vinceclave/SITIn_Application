<?php
session_start();
require_once 'config/config.php';

if (isset($_SESSION['idno'])) {
    $idno = $_SESSION['idno'];

    if (!empty($idno)) {
        // Update sit_in record: Set only the time (HH:MM:SS) for out_time and set status = 0 if out_time is filled
        $query = "
            UPDATE sit_in 
            SET out_time = TIME(NOW()), status = 0 
            WHERE idno = ? AND out_time IS NULL 
            ORDER BY sit_in_id DESC LIMIT 1
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $idno);
        $stmt->execute();
        $stmt->close();
    } else {
        error_log("Logout error: IDNO is empty.");
    }
} else {
    error_log("Logout error: No idno found in session.");
}

// Destroy session
session_unset();
session_destroy();

// Replace header redirection with HTML and Notiflix alert
?>
<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.5/dist/notiflix-aio-3.2.5.min.js"></script>
</head>
<body>
<script>
Notiflix.Notify.success('Logged out successfully!');
setTimeout(function() {
    window.location.href = 'login.php';
}, 2000);
</script>
</body>
</html>
<?php
exit;
?>
