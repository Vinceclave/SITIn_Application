<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ...retrieve and sanitize input...
    $id = intval($_POST['id']);
    $idno = mysqli_real_escape_string($conn, $_POST['idno']);
    $lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $middlename = mysqli_real_escape_string($conn, $_POST['middlename']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    
    // ...update query for student (only update if role is 'Student')...
    $query = "UPDATE users SET 
                idno = '$idno', 
                lastname = '$lastname', 
                firstname = '$firstname', 
                middlename = '$middlename', 
                username = '$username'
              WHERE id = $id AND role = 'Student'";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Student updated successfully.";
        header("Location: manage_users.php");
        exit();
    } else {
        $_SESSION['error'] = "Error updating record: " . mysqli_error($conn);
        header("Location: manage_users.php");
        exit();
    }
} else {
    header("Location: manage_users.php");
    exit();
}
?>
