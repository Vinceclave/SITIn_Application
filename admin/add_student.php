<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idno = mysqli_real_escape_string($conn, $_POST['idno']);
    $lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $middlename = mysqli_real_escape_string($conn, $_POST['middlename']);
    $course = mysqli_real_escape_string($conn, $_POST['course']);
    $year_level = mysqli_real_escape_string($conn, $_POST['year_level']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Check for duplicate username
    $dup_query = "SELECT id FROM users WHERE username='$username'";
    $dup_result = mysqli_query($conn, $dup_query);
    if (mysqli_num_rows($dup_result) > 0) {
        $_SESSION['error'] = "Error: Username already exists. Please choose a different username.";
        header("Location: manage_users.php");
        exit();
    }
    
    $query = "INSERT INTO users (idno, lastname, firstname, middlename, course, year_level, username, password, role)
              VALUES ('$idno', '$lastname', '$firstname', '$middlename', '$course', '$year_level', '$username', '$hashedPassword', 'Student')";
    
    if (mysqli_query($conn, $query)) {
        // Populate student session if not exists
        $checkSessionQuery = "SELECT * FROM student_session WHERE idno='$idno'";
        $sessionResult = mysqli_query($conn, $checkSessionQuery);
        if (mysqli_num_rows($sessionResult) == 0) {
            $defaultSessions = 30;
            $insertSessionQuery = "INSERT INTO student_session (idno, session) VALUES ('$idno', '$defaultSessions')";
            mysqli_query($conn, $insertSessionQuery);
        }
        $_SESSION['success'] = "Student added successfully.";
        header("Location: manage_users.php");
        exit();
    } else {
        $_SESSION['error'] = "Error adding student: " . mysqli_error($conn);
        header("Location: manage_users.php");
        exit();
    }
} else {
    header("Location: manage_users.php");
    exit();
}
?>