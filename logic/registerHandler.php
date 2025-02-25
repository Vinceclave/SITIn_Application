<?php
require_once '../config/config.php';
session_start();

// Retrieve form data
$idno = $_POST['idno'];
$lastname = $_POST['lastname'];
$firstname = $_POST['firstname'];
$middlename = $_POST['middlename'];
$course = $_POST['course'];
$year_level = $_POST['year_level'];
$username = $_POST['username'];
$password = $_POST['password'];

// Validate input
if (empty($idno) || empty($lastname) || empty($firstname) || empty($course) || empty($year_level) || empty($username) || empty($password)) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: ../register.php");
    exit;
}

if (strlen($password) < 6) {
    $_SESSION['error'] = "Password must be at least 6 characters long.";
    header("Location: ../register.php");
    exit;
}

// Hash the password
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Insert data into the database
$sql = "INSERT INTO students (idno, lastname, firstname, middlename, course, year_level, username, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issssiss", $idno, $lastname, $firstname, $middlename, $course, $year_level, $username, $hashed_password);

if ($stmt->execute()) {
    header("Location: ../login.php");
} else {
    $_SESSION['error'] = "Error: " . $stmt->error;
    header("Location: ../register.php");
}

$stmt->close();
$conn->close();
?>
