<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/config.php';
require_once '../shared/header.php';

// Fetch only students from the database
$query = "SELECT id, idno, lastname, firstname, middlename, course, year_level, username 
          FROM users 
          WHERE role = 'Student' 
          ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<?php include '../shared/aside.php'; ?>

<div class="ml-60 min-h-screen p-8"> 
    <h1 class="text-2xl font-semibold mb-4">Manage Students</h1>
    <p class="text-gray-600 mb-6">View and manage registered students below.</p>
    
    <div class="flex justify-end mb-4">
        <button onclick="openRegisterModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 transition">Add Student</button>
    </div>
    
    <!-- Student Management Table -->
    <div class="bg-white p-6 rounded-lg border border-gray-200 shadow-md">
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-200 text-gray-700">
                    <th class="px-4 py-2 border">ID</th>
                    <th class="px-4 py-2 border">ID Number</th>
                    <th class="px-4 py-2 border">Last Name</th>
                    <th class="px-4 py-2 border">First Name</th>
                    <th class="px-4 py-2 border">Middle Name</th>
                    <th class="px-4 py-2 border">Course</th>
                    <th class="px-4 py-2 border">Year Level</th>
                    <th class="px-4 py-2 border">Username</th>
                    <th class="px-4 py-2 border">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                        <tr class="text-center border-t">
                            <td class="px-4 py-2 border"><?= htmlspecialchars($row['id']) ?></td>
                            <td class="px-4 py-2 border"><?= htmlspecialchars($row['idno']) ?></td>
                            <td class="px-4 py-2 border"><?= htmlspecialchars($row['lastname']) ?></td>
                            <td class="px-4 py-2 border"><?= htmlspecialchars($row['firstname']) ?></td>
                            <td class="px-4 py-2 border"><?= htmlspecialchars($row['middlename']) ?></td>
                            <td class="px-4 py-2 border"><?= htmlspecialchars($row['course']) ?></td>
                            <td class="px-4 py-2 border"><?= htmlspecialchars($row['year_level']) ?></td>
                            <td class="px-4 py-2 border"><?= htmlspecialchars($row['username']) ?></td>
                            <td class="px-4 py-2 border">
                                <button onclick="openEditModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['idno']) ?>', '<?= htmlspecialchars($row['lastname']) ?>', '<?= htmlspecialchars($row['firstname']) ?>', '<?= htmlspecialchars($row['middlename']) ?>', '<?= htmlspecialchars($row['course']) ?>', '<?= htmlspecialchars($row['year_level']) ?>', '<?= htmlspecialchars($row['username']) ?>')" class="text-blue-600 hover:underline">Edit</button> | 
                                <a href="delete_user.php?id=<?= $row['id'] ?>" class="text-red-600 hover:underline" onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="px-4 py-4 border text-center text-gray-500">No students found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'register_modal.php'; ?>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg w-1/3">
        <h2 class="text-xl font-semibold mb-4">Edit Student</h2>
        <form action="update_student.php" method="POST" class="space-y-4">
            <input type="hidden" id="edit_id" name="id">

            <div>
                <label for="edit_idno" class="block text-sm font-medium text-gray-700">ID Number:</label>
                <input type="number" id="edit_idno" name="idno" class="w-full px-4 py-2 border rounded-lg">
            </div>

            <div class="grid gap-4 grid-cols-1 md:grid-cols-3">
                <div>
                    <label for="edit_lastname" class="block text-sm font-medium text-gray-700">Last Name:</label>
                    <input type="text" id="edit_lastname" name="lastname" class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label for="edit_firstname" class="block text-sm font-medium text-gray-700">First Name:</label>
                    <input type="text" id="edit_firstname" name="firstname" class="w-full px-4 py-2 border rounded-lg">
                </div>
                <div>
                    <label for="edit_middlename" class="block text-sm font-medium text-gray-700">Middle Name:</label>
                    <input type="text" id="edit_middlename" name="middlename" class="w-full px-4 py-2 border rounded-lg">
                </div>
            </div>

            <div>
                <label for="edit_username" class="block text-sm font-medium text-gray-700">Username:</label>
                <input type="text" id="edit_username" name="username" class="w-full px-4 py-2 border rounded-lg">
            </div>

            <div class="flex justify-end">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-gray-400 text-white rounded-lg mr-2">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 transition">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openRegisterModal() {
        document.getElementById("registerModal").classList.remove("hidden");
    }

    function openEditModal(id, idno, lastname, firstname, middlename, course, year_level, username) {
        document.getElementById("edit_id").value = id;
        document.getElementById("edit_idno").value = idno;
        document.getElementById("edit_lastname").value = lastname;
        document.getElementById("edit_firstname").value = firstname;
        document.getElementById("edit_middlename").value = middlename;
        document.getElementById("edit_username").value = username;
        document.getElementById("editModal").classList.remove("hidden");
    }

    function closeEditModal() {
        document.getElementById("editModal").classList.add("hidden");
    }
</script>

<?php require_once '../shared/footer.php'; ?>
