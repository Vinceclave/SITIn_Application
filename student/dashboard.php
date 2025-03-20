<?php
session_start();
require_once '../config/config.php';
require_once '../shared/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query); 
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Ensure role is set correctly
$role = isset($user['role']) ? $user['role'] : 'student';
?>

<div class="container mx-auto flex">
    <?php include '../shared/aside.php'; ?>
    <main class="pl-72 p-4">
        <h1 class="text-4xl font-bold mb-4">Dashboard</h1>
        <p>Welcome, <?php echo htmlspecialchars($user['username']); ?>!</p>

    </main>
</div>

<?php require_once '../shared/footer.php'; ?>
