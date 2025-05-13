<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/config.php';
require_once '../shared/header.php';

// Add search & pagination variables
$search = "";
if(isset($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
}
$perPage = 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $perPage;

// Count total students with filtering
$countQuery = "SELECT COUNT(*) as total FROM users WHERE role = 'Student' ";
if ($search) {
    $countQuery .= " AND (idno LIKE '%$search%' OR lastname LIKE '%$search%' OR firstname LIKE '%$search%')";
}
$countResult = mysqli_query($conn, $countQuery);
$totalRows = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalRows / $perPage);
    
// Fetch students with search filtering and pagination
$query = "SELECT * FROM users WHERE role = 'Student'";
if ($search) {
    $query .= " AND (idno LIKE '%$search%' OR lastname LIKE '%$search%' OR firstname LIKE '%$search%')";
}
$query .= " ORDER BY id DESC LIMIT $offset, $perPage";
$result = mysqli_query($conn, $query);

$students = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<div class="flex min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 text-gray-900 pb-14">
    <?php include '../shared/aside.php'; ?>
    <main class="flex-1 p-6 pt-24">
        <div class="max-w-7xl mx-auto">
            <!-- Page Header -->
            <div class="bg-white bg-opacity-80 backdrop-blur-sm rounded-xl shadow-md p-6 mb-8 border border-gray-100">
                <div class="flex items-center space-x-4">
                    <div class="bg-indigo-100 p-3 rounded-full">
                        <i class="fas fa-users text-2xl text-indigo-600"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Manage Students</h1>
                        <p class="text-lg text-gray-600">View and manage registered students</p>
                    </div>
                    <button onclick="openRegisterModal()" 
                            class="ml-auto px-5 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors shadow-sm flex items-center">
                        <i class="fas fa-user-plus mr-2"></i>Add Student
                    </button>
                </div>
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

            <!-- Search Bar -->
            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100 mb-6">
                <form action="manage_users.php" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="relative col-span-2">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" 
                               name="search" 
                               id="searchInput"
                               value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" 
                               placeholder="Search by ID, Last Name, or First Name" 
                               class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                    <div class="flex space-x-3">
                        <button type="submit" 
                                class="flex items-center justify-center px-5 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors shadow-sm w-1/2">
                            <i class="fas fa-search mr-2"></i>Search
                        </button>
                        <button type="button" 
                                onclick="clearSearch()" 
                                class="flex items-center justify-center px-5 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors shadow-sm w-1/2">
                            <i class="fas fa-eraser mr-2"></i>Clear
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Student Management Table -->
            <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Number</th>
                                <th class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Name</th>
                                <th class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">First Name</th>
                                <th class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Middle Name</th>
                                <th class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                                <th class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year Level</th>
                                <th class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                <th class="px-6 py-3.5 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (!empty($students)): ?>
                                <?php foreach ($students as $row): ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($row['id']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($row['idno']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($row['lastname']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($row['firstname']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($row['middlename']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($row['course']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($row['year_level']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= htmlspecialchars($row['username']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center space-x-4">
                                                <button onclick="openEditModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['idno']) ?>', '<?= htmlspecialchars($row['lastname']) ?>', '<?= htmlspecialchars($row['firstname']) ?>', '<?= htmlspecialchars($row['middlename']) ?>', '<?= htmlspecialchars($row['course']) ?>', '<?= htmlspecialchars($row['year_level']) ?>', '<?= htmlspecialchars($row['username']) ?>')" 
                                                        class="text-indigo-600 hover:text-indigo-900 transition-colors bg-indigo-50 p-1.5 rounded-md">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="delete_user.php?id=<?= $row['id'] ?>" 
                                                   class="text-red-600 hover:text-red-900 transition-colors bg-red-50 p-1.5 rounded-md delete-user">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="px-6 py-8 text-center text-sm text-gray-500">
                                        <div class="flex flex-col items-center justify-center py-8">
                                            <i class="fas fa-users-slash text-4xl text-gray-400 mb-3"></i>
                                            <p class="text-lg font-medium">No students found</p>
                                            <p class="text-gray-500 mt-1">Try adjusting your search parameters</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div id="pagination" class="flex justify-between items-center mt-6 bg-white p-4 rounded-xl shadow-md border border-gray-100">
                    <div class="text-sm text-gray-500 mr-4 flex items-center">
                        <span class="bg-indigo-100 text-indigo-800 py-1 px-3 rounded-full text-xs font-medium mr-2">
                            Page <?= $page ?> of <?= $totalPages ?>
                        </span>
                        <span><?= $totalRows ?> records found</span>
                    </div>
                    <div class="flex space-x-2">
                        <?php if ($page > 1): ?>
                            <a href="manage_users.php?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>" 
                               class="px-3 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors">
                                <i class="fas fa-chevron-left mr-1"></i> Prev
                            </a>
                        <?php endif; ?>
                        
                        <?php
                        $maxVisiblePages = 5;
                        $startPage = max(1, $page - floor($maxVisiblePages / 2));
                        $endPage = min($totalPages, $startPage + $maxVisiblePages - 1);
                        
                        // Adjust startPage if we are showing fewer than maxVisiblePages
                        if ($endPage - $startPage + 1 < $maxVisiblePages && $startPage > 1) {
                            $startPage = max(1, $endPage - $maxVisiblePages + 1);
                        }
                        
                        // First page and ellipsis if needed
                        if ($startPage > 1): ?>
                            <a href="manage_users.php?page=1<?= $search ? '&search=' . urlencode($search) : '' ?>" 
                               class="px-3 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors">
                                1
                            </a>
                            <?php if ($startPage > 2): ?>
                                <span class="px-3 py-2 text-gray-500">...</span>
                            <?php endif;
                        endif;
                        
                        // Page numbers
                        for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <a href="manage_users.php?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>" 
                               class="px-3 py-2 <?= ($i == $page ? 'bg-indigo-600 text-white' : 'bg-gray-200 hover:bg-gray-300') ?> rounded-md transition-colors">
                                <?= $i ?>
                            </a>
                        <?php endfor;
                        
                        // Last page and ellipsis if needed
                        if ($endPage < $totalPages): 
                            if ($endPage < $totalPages - 1): ?>
                                <span class="px-3 py-2 text-gray-500">...</span>
                            <?php endif; ?>
                            <a href="manage_users.php?page=<?= $totalPages ?><?= $search ? '&search=' . urlencode($search) : '' ?>" 
                               class="px-3 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors">
                                <?= $totalPages ?>
                            </a>
                        <?php endif;
                        
                        // Next button
                        if ($page < $totalPages): ?>
                            <a href="manage_users.php?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>" 
                               class="px-3 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors">
                                Next <i class="fas fa-chevron-right ml-1"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include 'register_modal.php'; ?>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md mx-4 transform transition-all">
        <div class="p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="text-xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-user-edit text-indigo-600 mr-2"></i>
                    Edit Student
                </h2>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="update_student.php" method="POST" class="space-y-5">
                <input type="hidden" id="edit_id" name="id">

                <div>
                    <label for="edit_idno" class="block text-sm font-medium text-gray-700 mb-1">ID Number</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-id-card text-gray-400"></i>
                        </div>
                        <input type="text" id="edit_idno" name="idno" 
                               class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <div class="grid gap-4 grid-cols-1 md:grid-cols-3">
                    <div>
                        <label for="edit_lastname" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                        <input type="text" id="edit_lastname" name="lastname" 
                               class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label for="edit_firstname" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                        <input type="text" id="edit_firstname" name="firstname" 
                               class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label for="edit_middlename" class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                        <input type="text" id="edit_middlename" name="middlename" 
                               class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <div>
                    <label for="edit_username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input type="text" id="edit_username" name="username" 
                               class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-3">
                    <button type="button" onclick="closeEditModal()" 
                            class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                        <i class="fas fa-save mr-2"></i>Update
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
        const searchInput = document.getElementById('searchInput');
        let searchTimeout;

        searchInput.addEventListener("input", function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const searchValue = searchInput.value;
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('search', searchValue);
                currentUrl.searchParams.set('page', '1'); // Reset to first page when searching
                window.location.href = currentUrl.toString();
            }, 300);
        });

        // Handle delete link clicks with Notiflix Confirm
        document.querySelectorAll('.delete-user').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                let url = this.getAttribute('href');
                Notiflix.Confirm.show(
                    'Confirm Deletion',
                    'Are you sure you want to delete this student? This action cannot be undone.',
                    'Yes, Delete',
                    'Cancel',
                    function() {
                        window.location.href = url;
                    },
                    function() {
                        Notiflix.Notify.info('Deletion canceled.');
                    },
                    {
                        titleColor: '#dc2626',
                        okButtonBackground: '#dc2626',
                        cancelButtonColor: '#374151'
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

    function clearSearch() {
        window.location.href = 'manage_users.php';
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
