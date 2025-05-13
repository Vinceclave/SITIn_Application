<?php
// Include necessary files like database connection, session, etc.
include '../shared/header.php';
include '../config/config.php';

// Start by handling the form submission (header redirect should be called first)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_resource'])) {
    $course_name = $_POST['course_name'];
    $link = $_POST['link'];
    $course_logo = $_FILES['course_logo'];
    $logoPath = 'uploads/logos/' . basename($course_logo['name']);

    // Check if the resource already exists (based on course name)
    $checkQuery = $conn->prepare("SELECT COUNT(*) FROM resources WHERE course_name = ?");
    $checkQuery->bind_param('s', $course_name);
    $checkQuery->execute();
    $checkQuery->bind_result($count);
    $checkQuery->fetch();
    $checkQuery->close();

    if ($count > 0) {
        $_SESSION['error'] = "A resource with this course name already exists.";
    } else {
        // Upload file (course_logo)
        if (move_uploaded_file($course_logo['tmp_name'], $logoPath)) {
            // Insert resource into the database
            $stmt = $conn->prepare("INSERT INTO resources (course_name, course_logo, link, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->bind_param('sss', $course_name, $logoPath, $link);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Resource added successfully.";
            } else {
                $_SESSION['error'] = "Error adding resource to the database.";
            }
            $stmt->close();
        } else {
            $_SESSION['error'] = "File upload failed. Please try again.";
        }
    }

    // Now we perform the redirect
    header('Location: ../admin/resources.php');
    exit();  // Ensures no further code is executed after the redirect
}

// Fetch resources from the database
$query = "SELECT * FROM resources ORDER BY created_at DESC";
$result = $conn->query($query);
$resources = $result->fetch_all(MYSQLI_ASSOC);
?>

<div class="flex min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 text-gray-900 pb-14">
    <?php include '../shared/aside.php'; ?>
    <main class="flex-1 p-6 pt-24">
        <div class="max-w-7xl mx-auto">

            <!-- Header -->
            <div class="bg-white bg-opacity-80 backdrop-blur-sm rounded-xl shadow-md p-6 mb-8 border border-gray-100">
                <h1 class="text-3xl font-bold text-gray-800 flex items-center space-x-3">
                    <i class="fas fa-book text-indigo-600"></i>
                    <span>Manage Resources</span>
                </h1>
                <p class="text-lg text-gray-600 mt-1">Create, edit, and delete learning materials</p>
            </div>

            <!-- Resource Actions -->
            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100 mb-8">
                <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Course Name</label>
                        <input type="text" name="course_name" class="w-full p-3 border border-gray-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                    </div>
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Course Logo</label>
                        <input type="file" name="course_logo" class="w-full p-2.5 border border-gray-200 rounded-lg bg-white file:bg-indigo-600 file:text-white file:border-none file:py-2 file:px-4 hover:file:bg-indigo-700 transition-all" required>
                    </div>
                    <div>
                        <label class="block mb-1 font-medium text-gray-700">Course Link</label>
                        <input type="url" name="link" class="w-full p-3 border border-gray-200 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" required>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" name="add_resource" class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all">
                            <i class="fas fa-plus-circle mr-2"></i>Add Resource
                        </button>
                    </div>
                </form>
            </div>

            <!-- Resource List -->
            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-list-ul text-blue-500 mr-2"></i>
                        Existing Resources
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-700">
                        <thead class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3">Course Name</th>
                                <th class="px-4 py-3">Course Logo</th>
                                <th class="px-4 py-3">Link</th>
                                <th class="px-4 py-3">Uploaded</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php foreach ($resources as $resource): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 font-medium text-gray-800"><?php echo htmlspecialchars($resource['course_name']); ?></td>
                                <td class="px-4 py-3">
                                    <img src="../uploads/logos/<?php echo htmlspecialchars($resource['course_logo']); ?>" alt="Logo" class="w-12 h-12 object-cover rounded-full">
                                </td>
                                <td class="px-4 py-3">
                                    <a href="<?php echo htmlspecialchars($resource['link']); ?>" class="text-indigo-600 hover:underline" target="_blank">
                                        View Course
                                    </a>
                                </td>
                                <td class="px-4 py-3"><?php echo date("M d, Y", strtotime($resource['created_at'])); ?></td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <a href="edit_resource.php?id=<?php echo $resource['id']; ?>" class="text-emerald-600 hover:text-emerald-800">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_resource.php?id=<?php echo $resource['id']; ?>" class="text-red-600 hover:text-red-800" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($resources)): ?>
                            <tr>
                                <td colspan="5" class="px-4 py-4 text-center text-gray-500">
                                    <i class="fas fa-info-circle mr-2"></i>No resources found.
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- Notifications for success/error -->
<?php if (isset($_SESSION['success'])): ?>
<script>
    Notiflix.Notify.success("<?php echo addslashes($_SESSION['success']); ?>");
</script>
<?php unset($_SESSION['success']); endif; ?>
<?php if (isset($_SESSION['error'])): ?>
<script>
    Notiflix.Notify.failure("<?php echo addslashes($_SESSION['error']); ?>");
</script>
<?php unset($_SESSION['error']); endif; ?>

<?php include '../shared/footer.php'; ?>
