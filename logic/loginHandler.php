<?php
session_start();
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $input = $_POST['username']; // Can be IDNO (Student) or username (Admin)
    $password = $_POST['password'];

    if (empty($input) || empty($password)) {
        $_SESSION['error'] = "Username/IDNO and password are required.";
        header("Location: ../login.php");
        exit;
    }

    // Check if user is Student (IDNO) or Admin (Username)
    if (is_numeric($input)) {
        // Student login (IDNO)
        $query = "SELECT id, idno, firstname, middlename, lastname, course, year_level, password, role FROM users WHERE idno = ?";
    } else {
        // Admin login (Username)
        $query = "SELECT id, username, password, role FROM users WHERE username = ?";
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            // Check role and set session variables accordingly.
            if ($user['role'] == 'Admin') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['success'] = "Admin login successful.";
                header("Location: ../admin/dashboard.php");
                exit;
            } elseif ($user['role'] == 'Student') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['idno'] = $user['idno'];
                $_SESSION['success'] = "Student login successful.";
                // 🔥 Update only `in_time` where it's NULL (most recent record)
                $updateTimeStmt = $conn->prepare("
                    UPDATE sit_in 
                    SET in_time = TIME(NOW()) 
                    WHERE idno = ? AND in_time IS NULL 
                    ORDER BY sit_in_id DESC LIMIT 1
                ");
                $updateTimeStmt->bind_param("s", $user['idno']);
                $updateTimeStmt->execute();

                // Changed redirect to student dashboard so the success alert is shown
                header("Location: ../student/home.php");
                exit;
            } else {
                $_SESSION['error'] = "Invalid role.";
            }
        } else {
            $_SESSION['error'] = "Invalid password.";
        }
    } else {
        $_SESSION['error'] = "No user found with that IDNO/Username.";
    }

    header("Location: ../login.php");
    exit;
}
?>
