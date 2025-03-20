<?php
session_start();
require_once 'config/config.php';

if (isset($_SESSION['idno'])) {
    $idno = $_SESSION['idno'];

    if (!empty($idno)) {
        // Remove the update for out_time and status fields
        // Just removing the reference to `out_time` and status update
    } else {
        error_log("Logout error: IDNO is empty.");
    }
} else {
    error_log("Logout error: No idno found in session.");
}

// Destroy session
session_unset();
session_destroy();

header("Location: login.php");
exit;
?>
