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

// Fetch student's remaining sessions
$idno = $user['idno']; // Assuming idno is stored in users table
$sessions_query = "SELECT session FROM student_session WHERE idno = ?";
$stmt = $conn->prepare($sessions_query);
$stmt->bind_param("s", $idno);
$stmt->execute();
$sessions_result = $stmt->get_result();
$remaining_sessions = $sessions_result->num_rows > 0 ? $sessions_result->fetch_assoc()['session'] : 0;

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
    
    // Basic server-side validation
    if (empty($lastname) || empty($firstname) || empty($username) || !ctype_digit($year_level)) {
        $alertError = "Please fill out all required fields correctly.";
    }
    
    // Default image path remains unchanged
    $image_path = $user['image_path'];
    
    // Handle image upload with validation if no prior error
    if (!isset($alertError) && isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            $alertError = "Invalid image type. Only JPG, PNG, and GIF are allowed.";
        } else {
            $uploads_dir = '../uploads';
            if (!is_dir($uploads_dir)) {
                mkdir($uploads_dir, 0777, true);
            }
            $targetFile = $uploads_dir . '/' . time() . '_' . basename($_FILES['image']['name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $image_path = $targetFile;
            } else {
                $alertError = "Failed to upload image.";
            }
        }
    }
    
    // Proceed only if no error was set during validation and file upload
    if (!isset($alertError)) {
        $update_query = "UPDATE users SET lastname = ?, firstname = ?, middlename = ?, course = ?, year_level = ?, username = ?, image_path = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ssssissi", $lastname, $firstname, $middlename, $course, $year_level, $username, $image_path, $user_id);
        if ($stmt->execute()) {
            $alertSuccess = "Profile updated successfully.";
            // Refresh user data
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            $alertError = "Failed to update profile.";
        }
    }
}
?>
<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.5/dist/notiflix-aio-3.2.5.min.js"></script>
<?php include '../shared/aside.php'; ?>

<main class="container max-w-[1400px] mx-auto mt-20 p-4 sm:p-6 md:p-8 lg:p-10">
    <section class="bg-white rounded-xl shadow-sm border border-gray-200/50 backdrop-blur-sm">
        <div class="p-6 sm:p-8 md:p-10">
            <div class="flex items-center gap-4 mb-8">
                <i class="fas fa-user-circle text-3xl sm:text-4xl text-indigo-600"></i>
                <div>
                    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">Profile Settings</h1>
                    <p class="text-gray-600 mt-1">Manage your account information</p>
                </div>
            </div>

            <form method="POST" id="profileForm" enctype="multipart/form-data" class="space-y-6">
                <!-- Profile Image Section -->
                <div class="flex flex-col items-center mb-8">
                    <div id="image-preview-container" class="relative group cursor-pointer">
                        <?php if ($user['image_path']): ?>
                            <img id="imgPreview" src="<?php echo htmlspecialchars($user['image_path']); ?>" alt="Profile Image" 
                                 class="w-32 h-32 rounded-full border-2 border-indigo-600 object-cover transition-transform group-hover:scale-105">
                        <?php else: ?>
                            <img id="imgPreview" src="../assets/default-profile.jpg" alt="Profile Image" 
                                 class="w-32 h-32 rounded-full border-2 border-indigo-600 object-cover transition-transform group-hover:scale-105">
                        <?php endif; ?>
                        <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity">
                            <i class="fas fa-camera text-white text-2xl"></i>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">Click to change profile picture</p>
                    <input type="file" id="image" name="image" class="hidden">
                </div>

                <!-- Personal Information -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label for="lastname" class="flex items-center text-gray-700 font-medium">
                            <i class="fas fa-user mr-2 text-indigo-600"></i>Last Name
                        </label>
                        <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($user['lastname']); ?>" 
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                    <div class="space-y-2">
                        <label for="firstname" class="flex items-center text-gray-700 font-medium">
                            <i class="fas fa-user mr-2 text-indigo-600"></i>First Name
                        </label>
                        <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($user['firstname']); ?>" 
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                    <div class="space-y-2">
                        <label for="middlename" class="flex items-center text-gray-700 font-medium">
                            <i class="fas fa-user mr-2 text-indigo-600"></i>Middle Name
                        </label>
                        <input type="text" id="middlename" name="middlename" value="<?php echo htmlspecialchars($user['middlename']); ?>" 
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <!-- Academic Information -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-2">
                        <label for="course" class="flex items-center text-gray-700 font-medium">
                            <i class="fas fa-graduation-cap mr-2 text-indigo-600"></i>Course
                        </label>
                        <input type="text" id="course" name="course" value="<?php echo htmlspecialchars($user['course']); ?>" 
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                    <div class="space-y-2">
                        <label for="year_level" class="flex items-center text-gray-700 font-medium">
                            <i class="fas fa-layer-group mr-2 text-indigo-600"></i>Year Level
                        </label>
                        <input type="number" id="year_level" name="year_level" value="<?php echo htmlspecialchars($user['year_level']); ?>" 
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <!-- Account Information -->
                <div class="space-y-2">
                    <label for="username" class="flex items-center text-gray-700 font-medium">
                        <i class="fas fa-at mr-2 text-indigo-600"></i>Username
                    </label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" 
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end pt-6">
                    <button type="button" id="updateProfileBtn" 
                            class="group relative inline-flex items-center justify-center px-8 py-3 font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-all duration-200 ease-in-out hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-save mr-2 group-hover:scale-110 transition-transform"></i>
                        Update Profile
                    </button>
                </div>
            </form>
        </div>
    </section>

    <div class="fixed top-20 right-6 p-4 bg-white rounded-xl shadow-sm border border-gray-200/50 backdrop-blur-sm">
        <div class="flex items-center space-x-3">
            <i class="fas fa-clock text-2xl text-indigo-600"></i>
            <div>
                <p class="text-sm text-gray-600">Remaining Sessions</p>
                <p class="text-2xl font-bold text-indigo-600"><?php echo $remaining_sessions; ?></p>
            </div>
        </div>
    </div>
</main>

<?php
// Use Notiflix for notifying the user
if (isset($alertSuccess)):
?>
<script>
Notiflix.Notify.success('<?php echo $alertSuccess; ?>');
</script>
<?php elseif (isset($alertError)): ?>
<script>
Notiflix.Notify.failure('<?php echo $alertError; ?>');
</script>
<?php endif; ?>
<!-- Image preview and clickable file input -->
<script>
document.getElementById('image-preview-container').addEventListener('click', function() {
    document.getElementById('image').click();
});
document.getElementById('image').addEventListener('change', function(event) {
    const file = event.target.files[0];
    const allowed = ['image/jpeg','image/png','image/gif'];
    if(file && allowed.includes(file.type)){
        const reader = new FileReader();
        reader.onload = function(e){
            document.getElementById('imgPreview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    } else {
        Notiflix.Notify.failure('Invalid image file selected.');
    }
});
</script>
<!-- New validation and modal confirmation script -->
<script>
document.getElementById('updateProfileBtn').addEventListener('click', function() {
    // Validate required fields
    var lastname = document.getElementById('lastname').value.trim();
    var firstname = document.getElementById('firstname').value.trim();
    var username = document.getElementById('username').value.trim();
    var year_level = document.getElementById('year_level').value.trim();
    if (lastname === "" || firstname === "" || username === "" || year_level === "" || isNaN(year_level)) {
        Notiflix.Notify.failure("Please fill out all required fields correctly.");
        return;
    }
    // Show confirmation modal
    Notiflix.Confirm.show(
        'Confirm Edit',
        'Are you sure you want to update your profile?',
        'Yes',
        'No',
        function() {
            document.getElementById('profileForm').submit();
        },
        function() {
            // ...existing code or no action...
        }
    );
});
</script>

<style>
    /* Add smooth transitions */
    .transition-all {
        transition-property: all;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 150ms;
    }
    
    /* Hover effects for inputs */
    input:focus, select:focus {
        outline: none;
        border-color: transparent;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
    }

    /* Button hover animation */
    button:hover {
        transform: translateY(-1px);
    }
</style>

<?php require_once '../shared/footer.php'; ?>
