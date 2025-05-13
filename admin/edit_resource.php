<?php
// Start output buffering at the beginning
ob_start();
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

// Check if admin
$role = isset($user['role']) ? $user['role'] : 'Student';
if (strcasecmp($role, 'Admin') !== 0) {
    header("Location: home.php");
    exit;
}

// Handle update form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_resource'])) {
    $id = $_POST['id'];
    $course_name = trim($_POST['course_name']);
    $link = trim($_POST['link']);
    $course_logo = isset($_FILES['course_logo']) ? $_FILES['course_logo'] : null;
    $old_logo = isset($_POST['old_logo']) ? $_POST['old_logo'] : '';

    // Validate input fields
    if (empty($course_name) || empty($link)) {
        $_SESSION['error'] = "All fields except logo are required.";
    } else {
        // Handle file upload
        $logoPath = $old_logo;  // Default to the old logo path

        // If a new logo is uploaded
        if ($course_logo && $course_logo['name']) {
            $logoPath = 'uploads/logos/' . basename($course_logo['name']);
            if (move_uploaded_file($course_logo['tmp_name'], $logoPath)) {
                // Delete old logo if it exists
                if ($old_logo && file_exists($old_logo)) {
                    unlink($old_logo);
                }
            } else {
                $_SESSION['error'] = "Failed to upload the logo.";
                header("Location: edit_resource.php?id=$id");
                exit;
            }
        }

        // Prepare and execute the update query
        $stmt = $conn->prepare("UPDATE resources SET course_name = ?, course_logo = ?, link = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("sssi", $course_name, $logoPath, $link, $id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Resource updated successfully.";
        } else {
            $_SESSION['error'] = "Update failed: " . $stmt->error;
        }
        $stmt->close();
    }
    header("Location: resources.php");
    exit;
}

// Fetch resource
$id = $_GET['id'] ?? 0;
$query = "SELECT * FROM resources WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$resource = $stmt->get_result()->fetch_assoc();
?>

<!-- Notification Scripts -->
<?php if (isset($_SESSION['success'])): ?>
<script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.5/dist/notiflix-aio-3.2.5.min.js"></script>
<script> Notiflix.Notify.success("<?php echo addslashes($_SESSION['success']); ?>"); </script>
<?php unset($_SESSION['success']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.5/dist/notiflix-aio-3.2.5.min.js"></script>
<script> Notiflix.Notify.failure("<?php echo addslashes($_SESSION['error']); ?>"); </script>
<?php unset($_SESSION['error']); endif; ?>

<!-- UI -->
<div class="flex min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 text-gray-900 pb-14">
    <?php include '../shared/aside.php'; ?>
    <main class="flex-1 p-6 pt-24">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white bg-opacity-80 backdrop-blur-sm rounded-xl shadow-md p-6 mb-8 border border-gray-100">
                <h1 class="text-3xl font-bold text-gray-800">Edit Resource</h1>
                <p class="text-lg text-gray-600">Update course resource details below.</p>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-md mb-8 border border-gray-100">
                <h2 class="text-xl font-bold text-gray-800 mb-6">Edit Resource</h2>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $resource['id']; ?>">
                    <input type="hidden" name="old_logo" value="<?php echo $resource['course_logo']; ?>">

                    <div class="space-y-4">
                        <input type="text" name="course_name" value="<?php echo htmlspecialchars($resource['course_name']); ?>" class="w-full p-4 border border-gray-200 rounded-lg" placeholder="Course Name" required>
                        <div>
                            <label for="course_logo" class="block text-gray-700">Course Logo</label>
                            <input type="file" name="course_logo" class="w-full p-4 border border-gray-200 rounded-lg">
                            <?php if ($resource['course_logo']): ?>
                                <p class="text-sm text-gray-500 mt-2">Current logo:</p>
                                <img src="../<?php echo $resource['course_logo']; ?>" alt="Logo" class="w-32 h-32 object-cover rounded-lg mt-2">
                            <?php endif; ?>
                        </div>
                        <input type="url" name="link" value="<?php echo htmlspecialchars($resource['link']); ?>" class="w-full p-4 border border-gray-200 rounded-lg" placeholder="Resource Link" required>
                        <button type="submit" name="update_resource" class="w-full px-5 py-3 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-colors">Update Resource</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<?php require_once '../shared/footer.php'; ?>

<?php
// End output buffering and send the output to the browser
ob_end_flush();
?>
