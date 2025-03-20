<?php
require_once '../config/config.php';
session_start();

// Retrieve form data
$idno = $_POST['idno'];
$lastname = $_POST['lastname'];
$firstname = $_POST['firstname'];
$middlename = $_POST['middlename'];
$username = $_POST['username'];
$password = $_POST['password'];
$course = $_POST['course'];
$year_level = $_POST['year_level'];

// Validate required fields
if (empty($idno) || empty($lastname) || empty($firstname) || empty($username) || empty($password) || empty($course) || empty($year_level)) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: ../register.php");
    exit;
}

// Validate password length
if (strlen($password) < 6) {
    $_SESSION['error'] = "Password must be at least 6 characters long.";
    header("Location: ../register.php");
    exit;
}

// Hash the password
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Force role to "student"
$role = "Student";

// Insert into the users table (only for students)
$sql = "INSERT INTO users (idno, lastname, firstname, middlename, username, password, course, year_level, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issssssss", $idno, $lastname, $firstname, $middlename, $username, $hashed_password, $course, $year_level, $role);

if ($stmt->execute()) {
    // ✅ After registering, check if the student already has a session record
    $checkSession = $conn->prepare("SELECT * FROM student_session WHERE idno = ?");
    $checkSession->bind_param("i", $idno);
    $checkSession->execute();
    $result = $checkSession->get_result();

    if ($result->num_rows == 0) {
        // ✅ Set the default session count to 30
        $defaultSessions = 30;
        $insertSession = $conn->prepare("INSERT INTO student_session (idno, session) VALUES (?, ?)");
        $insertSession->bind_param("ii", $idno, $defaultSessions);
        $insertSession->execute();
    }

    $checkSession->close();
    $insertSession->close();

    header("Location: ../login.php");
    exit;
} else {
    $_SESSION['error'] = "Error: " . $stmt->error;
    header("Location: ../register.php");
    exit;
}

$stmt->close();
$conn->close();
?>
