<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/config.php';
require_once '../shared/header.php';

// Fetch all users
$query = "SELECT *, id as id FROM users ORDER BY id DESC";
$result = mysqli_query($conn, $query);

//Check if there are error in the query
if (!$result) {
    die("Error in query: " . mysqli_error($conn));
}
?>

<div class="mt-10 flex min-h-screen bg-gray-50 text-gray-900 pb-14">
    <?php include '../shared/aside.php'; ?>
    <main class="flex-1 pt-10 p-6">
        <div class="max-w-[1400px] mx-auto">
            <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-semibold text-gray-800">Manage Users</h1>
                <p class="text-lg text-gray-600">View and manage all users</p>
            </div>
                <button onclick="openRegisterModal()" 
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-user-plus mr-2"></i>Add Student
                </button>
            </div>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                <?php 
                                echo htmlspecialchars($_SESSION['error']);
                                unset($_SESSION['error']);
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        


            
            <!-- Student Management Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">id</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">First Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Middle Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year Level</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (mysqli_num_rows($result) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr class="hover:bg-gray-50 transition-colors" >
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($row['id']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($row['idno']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($row['lastname']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($row['firstname']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($row['middlename']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($row['course']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($row['year_level']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($row['role']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($row['username']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div class="flex items-center space-x-3">
                                                <button onclick="openEditModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['idno']) ?>', '<?= htmlspecialchars($row['lastname']) ?>', '<?= htmlspecialchars($row['firstname']) ?>', '<?= htmlspecialchars($row['middlename']) ?>', '<?= htmlspecialchars($row['course']) ?>', '<?= htmlspecialchars($row['year_level']) ?>', '<?= htmlspecialchars($row['username']) ?>')" 
                                                        class="text-indigo-600 hover:text-indigo-900 transition-colors">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="delete_user.php?id=<?= $row['id'] ?>" 
                                                   class="text-red-600 hover:text-red-900 transition-colors delete-user">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="px-6 py-4 text-center text-sm text-gray-500">
                                        <div class="flex flex-col items-center justify-center py-8">
                                            <i class="fas fa-users-slash text-4xl text-gray-400 mb-2"></i>
                                            <p>No students found.</p>
                                        </div>
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

<?php include 'register_modal.php'; ?>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md mx-4 transform transition-all">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Edit Student</h2>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="update_student.php" method="POST" class="space-y-4">
                <input type="hidden" id="edit_id" name="id">

                <div>
                    <label for="edit_idno" class="block text-sm font-medium text-gray-700 mb-1">ID Number</label>
                    <input type="text" id="edit_idno" name="idno" 
                           class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                </div>

                <div class="grid gap-4 grid-cols-1 md:grid-cols-3">
                    <div>
                        <label for="edit_lastname" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                        <input type="text" id="edit_lastname" name="lastname" 
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label for="edit_firstname" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                        <input type="text" id="edit_firstname" name="firstname" 
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label for="edit_middlename" class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                        <input type="text" id="edit_middlename" name="middlename" 
                               class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <div>
                    <label for="edit_username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input type="text" id="edit_username" name="username" 
                           class="w-full px-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeEditModal()" 
                            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.5/dist/notiflix-aio-3.2.5.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {

        // Handle delete link clicks with Notiflix Confirm
        document.querySelectorAll('.delete-user').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                let url = this.getAttribute('href');
                Notiflix.Confirm.show(
                    'Confirm Deletion',
                    'Are you sure you want to delete this student?',
                    'Yes',
                    'No',
                    function() {
                        window.location.href = url;
                    },
                    function() {
                        Notiflix.Notify.info('Deletion canceled.');
                    }
                );
            });
        });
        
        // Validate and trap errors on Edit modal form submission
        const editForm = document.querySelector('form[action="update_student.php"]');
        if(editForm) {
            editForm.addEventListener('submit', function(e) {
                let idno = document.getElementById('edit_idno').value.trim();
                let lastname = document.getElementById('edit_lastname').value.trim();
                let firstname = document.getElementById('edit_firstname').value.trim();
                let middlename = document.getElementById('edit_middlename').value.trim();
                let username = document.getElementById('edit_username').value.trim();
                if(!idno || !lastname || !firstname || !middlename || !username) {
                    e.preventDefault();
                    Notiflix.Notify.warning('Please fill in all required fields.');
                }
            });
        }
    });

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

    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    navy: "#24292e",
                    darkblue: "#0366d6",
                    steelblue: "#f6f8fa",
                    bluegray: "#6a737d"
                }
            }
        }
    }
</script>

<?php require_once '../shared/footer.php'; ?>
