<?php
session_start();
require_once '../config/config.php';
require_once '../shared/header.php';

if(isset($_SESSION['success'])):
?>
<script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.5/dist/notiflix-aio-3.2.5.min.js"></script>
<script>
    Notiflix.Notify.success("<?php echo addslashes($_SESSION['success']); ?>");
</script>
<?php unset($_SESSION['success']); endif; ?>

<?php
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

<div class="container max-w-[1400px] mx-auto mt-20 flex flex-col md:flex-row p-4 sm:p-6 md:p-8 lg:p-10">
    <?php include '../shared/aside.php'; ?>
    <main class="w-full">
        <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold mb-6">Dashboard</h1>
        <p class="text-base sm:text-lg md:text-xl mb-6">
            Welcome, <?php echo htmlspecialchars($user['username']); ?>!
        </p>
    </main>
</div>

<?php require_once '../shared/footer.php'; ?>
