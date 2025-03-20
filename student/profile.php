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

// Ensure role is set
$role = isset($user['role']) ? $user['role'] : 'student';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lastname = $_POST['lastname'];
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'];
    $course = $_POST['course'];
    $year_level = $_POST['year_level'];
    $username = $_POST['username'];

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploads_dir = '../uploads';
        if (!is_dir($uploads_dir)) {
            mkdir($uploads_dir, 0777, true);
        }
        $image_path = $uploads_dir . '/' . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            // Update the image path in the database
            $update_query = "UPDATE users    SET image_path = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("si", $image_path, $user_id);
            $stmt->execute();
        } else {
            $image_path = $user['image_path'];
        }
    } else {
        $image_path = $user['image_path'];
    }

    $update_query = "UPDATE users SET lastname = ?, firstname = ?, middlename = ?, course = ?, year_level = ?, username = ?, image_path = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssssissi", $lastname, $firstname, $middlename, $course, $year_level, $username, $image_path, $user_id);
    $stmt->execute();

    // Refresh user data
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}

?>
    <?php include '../shared/aside.php'; ?>
    <main class="">
        <section class="relative px-10 py-4">
            <h1 class="text-4xl font-bold mb-4">Profile</h1>
            <p class="mb-8">Welcome to your profile, <?php echo htmlspecialchars($user['username']); ?>!</p>
            <form method="POST" enctype="multipart/form-data">
                <div class="flex">
                    <div class="mb-4">
                        <label for="lastname" class="block text-gray-700">Last Name</label>
                        <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($user['lastname']); ?>" class="mt-1 block w-full">
                    </div>
                    <div class="mb-4">
                        <label for="firstname" class="block text-gray-700">First Name</label>
                        <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($user['firstname']); ?>" class="mt-1 block w-full">
                    </div>
                    <div class="mb-4">
                        <label for="middlename" class="block text-gray-700">Middle Name</label>
                        <input type="text" id="middlename" name="middlename" value="<?php echo htmlspecialchars($user['middlename']); ?>" class="mt-1 block w-full">
                    </div>
                </div>
                <div class="mb-4">
                    <label for="course" class="block text-gray-700">Course</label>
                    <input type="text" id="course" name="course" value="<?php echo htmlspecialchars($user['course']); ?>" class="mt-1 block w-full" >
                </div>
                <div class="mb-4">
                    <label for="year_level" class="block text-gray-700">Year Level</label>
                    <input type="number" id="year_level" name="year_level"  value="<?php echo htmlspecialchars($user['year_level']); ?>" class="mt-1 block w-full">
                </div>
                <div class="mb-4">
                    <label for="username" class="block text-gray-700">Username</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="mt-1 block w-full">
                </div>
                <div class="mb-4">
                    <label for="image" class="block text-gray-700">Profile Image</label>
                    <input type="file" id="image" name="image" class="mt-1 block w-full">
                    <?php if ($user['image_path']): ?>
                        <img src="<?php echo htmlspecialchars($user['image_path']); ?>" alt="Profile Image" class="mt-2 w-32 h-32 object-cover">
                    <?php endif; ?>
                </div>
                <button type="submit" class="bg-blue-500 text-white px-4 py-2">Update Profile</button>
            </form>
        </section>

        <div class="absolute top-10 right-10 flex items-center space-x-2">
            <!-- Icon -->
            <svg class="w-6 h-6 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>

            <!-- Text -->
            <h4 class="text-lg font-semibold text-gray-800">
                Remaining Sessions: <span class="text-red-500">0</span>
            </h4>
        </div>
    </main>
<?php
require_once '../shared/footer.php';
?>
