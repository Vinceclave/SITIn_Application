<?php
session_start();
require_once '../config/config.php';

// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

// Handle form submission for adding new resource
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $course_name = mysqli_real_escape_string($conn, $_POST['course_name']);
        $link = mysqli_real_escape_string($conn, $_POST['link']);
        
        // Handle file upload for course logo
        $course_logo = "";
        if (isset($_FILES['course_logo']) && $_FILES['course_logo']['error'] === 0) {
            $upload_dir = "../uploads/";
            $temp_name = $_FILES['course_logo']['tmp_name'];
            $original_name = $_FILES['course_logo']['name'];
            $file_extension = pathinfo($original_name, PATHINFO_EXTENSION);
            $new_file_name = time() . '_' . $original_name;
            
            // Move uploaded file
            if (move_uploaded_file($temp_name, $upload_dir . $new_file_name)) {
                $course_logo = $new_file_name;
            }
        }
        
        // Insert new resource
        $insert_query = "INSERT INTO resources (course_name, course_logo, link) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("sss", $course_name, $course_logo, $link);
          if ($stmt->execute()) {
            $_SESSION['success'] = "Resource added successfully!";
        } else {
            $_SESSION['error'] = "Error adding resource: " . $conn->error;
        }
        
        header("Location: manage_resources.php");
        exit();
    } elseif ($_POST['action'] === 'edit') {
        $id = intval($_POST['id']);
        $course_name = mysqli_real_escape_string($conn, $_POST['course_name']);
        $link = mysqli_real_escape_string($conn, $_POST['link']);
        
        // Check if there's a new file upload
        if (isset($_FILES['course_logo']) && $_FILES['course_logo']['error'] === 0) {
            $upload_dir = "../uploads/";
            $temp_name = $_FILES['course_logo']['tmp_name'];
            $original_name = $_FILES['course_logo']['name'];
            $new_file_name = time() . '_' . $original_name;
            
            // Move uploaded file
            if (move_uploaded_file($temp_name, $upload_dir . $new_file_name)) {
                // Update with new logo
                $update_query = "UPDATE resources SET course_name = ?, course_logo = ?, link = ? WHERE id = ?";
                $stmt = $conn->prepare($update_query);
                $stmt->bind_param("sssi", $course_name, $new_file_name, $link, $id);
            }
        } else {
            // Update without changing logo
            $update_query = "UPDATE resources SET course_name = ?, link = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssi", $course_name, $link, $id);
        }
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Resource updated successfully!";
        } else {
            $_SESSION['error'] = "Error updating resource: " . $conn->error;
        }
        
        header("Location: manage_resources.php");
        exit();
    } elseif ($_POST['action'] === 'delete') {
        $id = intval($_POST['id']);
        
        // Get the file path before deleting the record
        $file_query = "SELECT course_logo FROM resources WHERE id = ?";
        $file_stmt = $conn->prepare($file_query);
        $file_stmt->bind_param("i", $id);
        $file_stmt->execute();
        $file_result = $file_stmt->get_result();
        
        if ($file_result->num_rows > 0) {
            $file_data = $file_result->fetch_assoc();
            $file_to_delete = "../uploads/" . $file_data['course_logo'];
            
            // Delete file if it exists
            if (!empty($file_data['course_logo']) && file_exists($file_to_delete)) {
                unlink($file_to_delete);
            }
        }
        
        // Delete record
        $delete_query = "DELETE FROM resources WHERE id = ?";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Resource deleted successfully!";
        } else {
            $_SESSION['error'] = "Error deleting resource: " . $conn->error;
        }
        
        header("Location: manage_resources.php");
        exit();
    }
}

// Check if resources table exists, if not create it
$check_table_query = "SHOW TABLES LIKE 'resources'";
$table_result = $conn->query($check_table_query);

if ($table_result->num_rows === 0) {
    // Create resources table
    $sql_file = file_get_contents('../database/resources_table.sql');
    
    // Split statements by semicolon
    $statements = explode(';', $sql_file);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $conn->query($statement);
        }
    }
}

// Get resources with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Count total resources
$count_query = "SELECT COUNT(*) as total FROM resources";
$count_result = $conn->query($count_query);
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Get resources for current page
$resources_query = "SELECT * FROM resources ORDER BY created_at DESC LIMIT ? OFFSET ?";
$resources_stmt = $conn->prepare($resources_query);
$resources_stmt->bind_param("ii", $limit, $offset);
$resources_stmt->execute();
$resources_result = $resources_stmt->get_result();

// Include header after all processing is done
require_once '../shared/header.php';
?>

<div class="flex min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 text-gray-900 pb-14">
    <?php include '../shared/aside.php'; ?>
    <main class="flex-1 p-6 pt-24">
        <div class="max-w-7xl mx-auto">
            <!-- Welcome Section -->
            <div class="bg-white bg-opacity-80 backdrop-blur-sm rounded-xl shadow-md p-6 mb-8 border border-gray-100">
                <div class="flex items-center space-x-4">
                    <div class="bg-indigo-100 p-3 rounded-full">
                        <i class="fas fa-book text-2xl text-indigo-600"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Manage Resources</h1>
                        <p class="text-lg text-gray-600">Add, edit, or delete educational resources for students.</p>
                    </div>
                </div>
            </div>

            <!-- Success or Error Messages -->
            <?php if (isset($_SESSION['success'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-md" role="alert">
                <p><?php echo $_SESSION['success']; ?></p>
            </div>
            <?php unset($_SESSION['success']); endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-md" role="alert">
                <p><?php echo $_SESSION['error']; ?></p>
            </div>
            <?php unset($_SESSION['error']); endif; ?>

            <!-- Add Resource Form -->
            <div class="bg-white rounded-xl shadow-md mb-8 overflow-hidden">
                <div class="bg-indigo-600 p-4 text-white font-medium">
                    <h2 class="text-lg">Add New Resource</h2>
                </div>
                <div class="p-6">
                    <form action="manage_resources.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label for="course_name" class="block text-sm font-medium text-gray-700 mb-1">Course/Resource Name</label>
                                <input type="text" id="course_name" name="course_name" required
                                    class="w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            
                            <div>
                                <label for="course_logo" class="block text-sm font-medium text-gray-700 mb-1">Logo/Image</label>
                                <input type="file" id="course_logo" name="course_logo" accept="image/*"
                                    class="w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <p class="text-xs text-gray-500 mt-1">Optional. Upload an image representing this resource.</p>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <label for="link" class="block text-sm font-medium text-gray-700 mb-1">Resource Link</label>
                            <input type="url" id="link" name="link" required
                                class="w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="text-xs text-gray-500 mt-1">Enter a full URL (e.g., https://example.com/resource)</p>
                        </div>
                        
                        <div>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Add Resource
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Resources List Table -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="bg-indigo-600 p-4 text-white font-medium">
                    <h2 class="text-lg">Resources List</h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course/Resource</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Logo</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Link</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated At</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if ($resources_result->num_rows > 0): ?>
                                <?php while ($resource = $resources_result->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $resource['id']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($resource['course_name']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if (!empty($resource['course_logo'])): ?>
                                                <img src="../uploads/<?php echo $resource['course_logo']; ?>" alt="Logo" class="h-10 w-auto">
                                            <?php else: ?>
                                                <span class="text-gray-400">No logo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600">
                                            <a href="<?php echo htmlspecialchars($resource['link']); ?>" target="_blank" class="hover:underline truncate block max-w-xs">
                                                <?php echo htmlspecialchars($resource['link']); ?>
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M d, Y h:i A', strtotime($resource['created_at'])); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('M d, Y h:i A', strtotime($resource['updated_at'])); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="openEditModal(<?php echo $resource['id']; ?>, '<?php echo addslashes($resource['course_name']); ?>', '<?php echo addslashes($resource['link']); ?>')" 
                                                    class="text-indigo-600 hover:text-indigo-900 mr-3">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button onclick="confirmDelete(<?php echo $resource['id']; ?>, '<?php echo addslashes($resource['course_name']); ?>')" 
                                                    class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash-alt"></i> Delete
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No resources found. Add your first resource above.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex justify-between">
                        <div>
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>" class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-md hover:bg-indigo-200">
                                    Previous
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="text-gray-600">
                            Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                        </div>
                        <div>
                            <?php if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?>" class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-md hover:bg-indigo-200">
                                    Next
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center px-4 z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
        <div class="bg-indigo-600 text-white py-3 px-4 rounded-t-lg">
            <h3 class="text-lg font-medium">Edit Resource</h3>
        </div>
        <form action="manage_resources.php" method="POST" enctype="multipart/form-data" class="p-6">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" id="edit_id">
            
            <div class="mb-4">
                <label for="edit_course_name" class="block text-sm font-medium text-gray-700 mb-1">Course/Resource Name</label>
                <input type="text" id="edit_course_name" name="course_name" required
                    class="w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            
            <div class="mb-4">
                <label for="edit_course_logo" class="block text-sm font-medium text-gray-700 mb-1">Logo/Image</label>
                <input type="file" id="edit_course_logo" name="course_logo" accept="image/*"
                    class="w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                <p class="text-xs text-gray-500 mt-1">Leave empty to keep current image</p>
            </div>
            
            <div class="mb-6">
                <label for="edit_link" class="block text-sm font-medium text-gray-700 mb-1">Resource Link</label>
                <input type="url" id="edit_link" name="link" required
                    class="w-full py-2 px-3 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeEditModal()" 
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex items-center justify-center px-4 z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
        <div class="bg-red-600 text-white py-3 px-4 rounded-t-lg">
            <h3 class="text-lg font-medium">Confirm Deletion</h3>
        </div>
        <div class="p-6">
            <p class="mb-6 text-gray-700">Are you sure you want to delete the resource "<span id="delete_resource_name"></span>"? This action cannot be undone.</p>
            
            <form action="manage_resources.php" method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete_id">
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeDeleteModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEditModal(id, courseName, link) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_course_name').value = courseName;
    document.getElementById('edit_link').value = link;
    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('editModal').classList.add('flex');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('flex');
    document.getElementById('editModal').classList.add('hidden');
}

function confirmDelete(id, courseName) {
    document.getElementById('delete_id').value = id;
    document.getElementById('delete_resource_name').textContent = courseName;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.getElementById('deleteModal').classList.add('flex');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('flex');
    document.getElementById('deleteModal').classList.add('hidden');
}

// Close modals when clicking outside
window.addEventListener('click', function(event) {
    let editModal = document.getElementById('editModal');
    let deleteModal = document.getElementById('deleteModal');
    
    if (event.target === editModal) {
        closeEditModal();
    }
    
    if (event.target === deleteModal) {
        closeDeleteModal();
    }
});
</script>

<?php require_once '../shared/footer.php'; ?>
