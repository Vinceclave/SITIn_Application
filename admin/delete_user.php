<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "DELETE FROM users WHERE id = $id AND role = 'Student'";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Student deleted successfully.";
        header("Location: manage_users.php");
        exit();
    } else {
        $_SESSION['error'] = "Error deleting record: " . mysqli_error($conn);
        header("Location: manage_users.php");
        exit();
    }
} else {
    header("Location: manage_users.php");
    exit();
}
?>
