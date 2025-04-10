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
$query = "SELECT * FROM students WHERE id = ?";
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
    $image_path = $_POST['selectedImage'];

    $update_query = "UPDATE students SET lastname = ?, firstname = ?, middlename = ?, course = ?, year_level = ?, username = ?, image_path = ? WHERE id = ?";
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

<div class="container mx-auto p-4 flex">
    <?php include '../shared/aside.php'; ?>
    <main class="ml-64 p-6">
        <!-- GitHub-like Profile Card -->
        <div class="bg-white border rounded-lg shadow-md">
            <div class="border-b px-6 py-4">
                <div class="flex items-center space-x-6">
                    <?php if ($user['image_path']): ?>
                        <img src="<?php echo htmlspecialchars($user['image_path']); ?>" alt="Profile Image" class="w-20 h-20 rounded-full object-cover">
                    <?php else: ?>
                        <div class="w-20 h-20 rounded-full bg-gray-300"></div>
                    <?php endif; ?>
                    <div>
                        <p class="text-xl font-semibold"><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></p>
                        <p class="text-gray-600">@<?php echo htmlspecialchars($user['username']); ?></p>
                    </div>
                </div>
                <h1 class="mt-4 text-2xl font-bold">Profile</h1>
            </div>
            <div class="p-6">
                <p class="mb-6">Welcome to your profile, <?php echo htmlspecialchars($user['username']); ?>!</p>
                <form method="POST" enctype="multipart/form-data">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="lastname" class="block text-gray-700">Last Name</label>
                            <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($user['lastname']); ?>" class="mt-1 block w-full border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label for="firstname" class="block text-gray-700">First Name</label>
                            <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($user['firstname']); ?>" class="mt-1 block w-full border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label for="middlename" class="block text-gray-700">Middle Name</label>
                            <input type="text" id="middlename" name="middlename" value="<?php echo htmlspecialchars($user['middlename']); ?>" class="mt-1 block w-full border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label for="course" class="block text-gray-700">Course</label>
                            <input type="text" id="course" name="course" value="<?php echo htmlspecialchars($user['course']); ?>" class="mt-1 block w-full border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label for="year_level" class="block text-gray-700">Year Level</label>
                            <input type="number" id="year_level" name="year_level" value="<?php echo htmlspecialchars($user['year_level']); ?>" class="mt-1 block w-full border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label for="username" class="block text-gray-700">Username</label>
                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" class="mt-1 block w-full border-gray-300 rounded-md">
                        </div>
                    </div>
                    <!-- Image Selection Gallery -->
                    <div class="mb-4">
                        <label class="block text-gray-700">Select Profile Image</label>
                        <div class="grid grid-cols-3 gap-4 mt-2">
                            <img src="../uploads/default1.png" alt="Default 1" class="w-20 h-20 object-cover rounded-full cursor-pointer border-2 border-transparent hover:border-blue-500 select-image" data-image="../uploads/default1.png">
                            <img src="../uploads/default2.png" alt="Default 2" class="w-20 h-20 object-cover rounded-full cursor-pointer border-2 border-transparent hover:border-blue-500 select-image" data-image="../uploads/default2.png">
                            <img src="../uploads/default3.png" alt="Default 3" class="w-20 h-20 object-cover rounded-full cursor-pointer border-2 border-transparent hover:border-blue-500 select-image" data-image="../uploads/default3.png">
                        </div>
                        <input type="hidden" name="selectedImage" id="selectedImage" value="<?php echo htmlspecialchars($user['image_path']); ?>">
                    </div>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">Update Profile</button>
                </form>
                <script>
                    document.querySelectorAll('.select-image').forEach(img => {
                        img.addEventListener('click', function() {
                            document.querySelectorAll('.select-image').forEach(i => {
                                i.classList.remove('ring-2', 'ring-blue-500');
                            });
                            this.classList.add('ring-2', 'ring-blue-500');
                            document.getElementById('selectedImage').value = this.getAttribute('data-image');
                        });
                    });
                </script>
            </div>
        </div>
    </main>
</div>

<?php
require_once '../shared/footer.php';
?>
