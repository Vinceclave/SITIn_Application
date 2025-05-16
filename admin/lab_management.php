<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/config.php';
require_once '../shared/header.php';

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add new lab
    if (isset($_POST['add_lab'])) {
        $lab_name = trim($_POST['lab_name']);
        $total_pcs = (int)$_POST['total_pcs'];
        $location = trim($_POST['location']);
        $status = $_POST['status'];
        
        // Validate input
        if (empty($lab_name) || $total_pcs < 1 || empty($location)) {
            $errorMessage = "Please fill all required fields with valid values.";
        } else {
            // Check if lab already exists
            $checkQuery = "SELECT * FROM labs WHERE lab_name = ?";
            $checkStmt = $conn->prepare($checkQuery);
            $checkStmt->bind_param("s", $lab_name);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                $errorMessage = "A lab with this name already exists.";
            } else {
                // Insert new lab
                $insertQuery = "INSERT INTO labs (lab_name, total_pcs, location, status) VALUES (?, ?, ?, ?)";
                $insertStmt = $conn->prepare($insertQuery);
                $insertStmt->bind_param("siss", $lab_name, $total_pcs, $location, $status);
                
                if ($insertStmt->execute()) {
                    $successMessage = "Lab added successfully!";
                } else {
                    $errorMessage = "Error adding lab: " . $insertStmt->error;
                }
            }
        }
    }
    
    // Update lab
    if (isset($_POST['update_lab'])) {
        $lab_id = (int)$_POST['lab_id'];
        $total_pcs = (int)$_POST['total_pcs'];
        $location = trim($_POST['location']);
        $status = $_POST['status'];
        
        // Validate input
        if ($lab_id < 1 || $total_pcs < 1 || empty($location)) {
            $errorMessage = "Please fill all required fields with valid values.";
        } else {
            // Update lab
            $updateQuery = "UPDATE labs SET total_pcs = ?, location = ?, status = ? WHERE lab_id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("issi", $total_pcs, $location, $status, $lab_id);
            
            if ($updateStmt->execute()) {
                $successMessage = "Lab updated successfully!";
            } else {
                $errorMessage = "Error updating lab: " . $updateStmt->error;
            }
        }
    }
    
    // Delete lab
    if (isset($_POST['delete_lab'])) {
        $lab_id = (int)$_POST['lab_id'];
        
        // Check if there are any reservations for this lab
        $checkQuery = "SELECT COUNT(*) as count FROM reservations 
                        JOIN labs ON reservations.lab_name = labs.lab_name 
                        WHERE labs.lab_id = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("i", $lab_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $row = $checkResult->fetch_assoc();
        
        if ($row['count'] > 0) {
            $errorMessage = "Cannot delete this lab because it has active reservations. Consider setting it to 'unavailable' instead.";
        } else {
            // Delete lab
            $deleteQuery = "DELETE FROM labs WHERE lab_id = ?";
            $deleteStmt = $conn->prepare($deleteQuery);
            $deleteStmt->bind_param("i", $lab_id);
            
            if ($deleteStmt->execute()) {
                $successMessage = "Lab deleted successfully!";
            } else {
                $errorMessage = "Error deleting lab: " . $deleteStmt->error;
            }
        }
    }
}

// Fetch all labs
$labsQuery = "SELECT * FROM labs ORDER BY lab_name";
$labsResult = $conn->query($labsQuery);
$labs = [];
while ($lab = $labsResult->fetch_assoc()) {
    $labs[] = $lab;
}

// Get counts of reservations per lab
$labStatsQuery = "SELECT labs.lab_name, COUNT(reservations.reservation_id) AS reservation_count 
                  FROM labs 
                  LEFT JOIN reservations ON labs.lab_name = reservations.lab_name 
                  WHERE reservations.status IN ('pending', 'approved') OR reservations.status IS NULL
                  GROUP BY labs.lab_name";
$labStatsResult = $conn->query($labStatsQuery);
$labStats = [];
while ($stat = $labStatsResult->fetch_assoc()) {
    $labStats[$stat['lab_name']] = $stat['reservation_count'];
}
?>

<?php if(isset($successMessage)): ?>
<script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.5/dist/notiflix-aio-3.2.5.min.js"></script>
<script>
    Notiflix.Notify.success("<?php echo addslashes($successMessage); ?>");
</script>
<?php endif; ?>

<?php if(isset($errorMessage)): ?>
<script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.5/dist/notiflix-aio-3.2.5.min.js"></script>
<script>
    Notiflix.Notify.failure("<?php echo addslashes($errorMessage); ?>");
</script>
<?php endif; ?>

<div class="flex min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 text-gray-900 pb-14">
    <?php include '../shared/aside.php'; ?>
    <main class="flex-1 p-6 pt-24">
        <div class="max-w-7xl mx-auto">
            <!-- Welcome Section -->
            <div class="bg-white bg-opacity-80 backdrop-blur-sm rounded-xl shadow-md p-6 mb-8 border border-gray-100">
                <div class="flex items-center space-x-4">
                    <div class="bg-indigo-100 p-3 rounded-full">
                        <i class="fas fa-flask text-2xl text-indigo-600"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Lab Management</h1>
                        <p class="text-lg text-gray-600">Add, edit or disable computer laboratories</p>
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end mb-6">
                <button id="addLabBtn" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>Add New Lab
                </button>
            </div>
            
            <!-- Add Lab Form (hidden by default) -->
            <div id="addLabForm" class="bg-white rounded-xl shadow-md p-6 mb-6 border border-gray-100 hidden">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Add New Lab</h2>
                <form method="POST">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label for="lab_name" class="block text-sm font-medium text-gray-700 mb-2">Lab Name/Number</label>
                            <input type="text" id="lab_name" name="lab_name" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label for="total_pcs" class="block text-sm font-medium text-gray-700 mb-2">Total PCs</label>
                            <input type="number" id="total_pcs" name="total_pcs" min="1" value="50" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                            <input type="text" id="location" name="location" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select id="status" name="status" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                                <option value="available">Available</option>
                                <option value="unavailable">Unavailable</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-end">
                        <button type="button" id="cancelAddBtn" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors mr-2">
                            Cancel
                        </button>
                        <button type="submit" name="add_lab" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            Add Lab
                        </button>
                    </div>
                </form>
            </div>            <!-- Labs Table -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6 border border-gray-100">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Labs Overview</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lab Name/Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total PCs</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active Reservations</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($labs)): ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No labs found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($labs as $lab): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($lab['lab_name']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($lab['total_pcs']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($lab['location']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($lab['status'] === 'available'): ?>
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                    Available
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                    Unavailable
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php echo isset($labStats[$lab['lab_name']]) ? $labStats[$lab['lab_name']] : 0; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                                            <button class="edit-lab-btn text-blue-600 hover:text-blue-900" 
                                                    data-id="<?php echo $lab['lab_id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($lab['lab_name']); ?>"
                                                    data-pcs="<?php echo $lab['total_pcs']; ?>"
                                                    data-location="<?php echo htmlspecialchars($lab['location']); ?>"
                                                    data-status="<?php echo $lab['status']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="delete-lab-btn text-red-600 hover:text-red-900" 
                                                    data-id="<?php echo $lab['lab_id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($lab['lab_name']); ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Edit Lab Modal -->
<div id="editLabModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-lg">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Edit Lab</h2>
        <form method="POST">
            <input type="hidden" id="edit_lab_id" name="lab_id">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Lab Name/Number</label>
                <input type="text" id="edit_lab_name" class="w-full rounded-lg border border-gray-300 px-4 py-2 bg-gray-100" readonly>
            </div>
            <div class="mb-4">
                <label for="edit_total_pcs" class="block text-sm font-medium text-gray-700 mb-2">Total PCs</label>
                <input type="number" id="edit_total_pcs" name="total_pcs" min="1" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
            </div>
            <div class="mb-4">
                <label for="edit_location" class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                <input type="text" id="edit_location" name="location" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
            </div>
            <div class="mb-4">
                <label for="edit_status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select id="edit_status" name="status" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                    <option value="available">Available</option>
                    <option value="unavailable">Unavailable</option>
                </select>
            </div>
            <div class="flex justify-end">
                <button type="button" id="cancelEditBtn" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors mr-2">
                    Cancel
                </button>
                <button type="submit" name="update_lab" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                    Update Lab
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Lab Confirmation Modal -->
<div id="deleteLabModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-xl p-6 w-full max-w-md">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Confirm Deletion</h2>
        <p class="mb-4">Are you sure you want to delete lab <span id="delete_lab_name" class="font-semibold"></span>? This action cannot be undone.</p>
        <form method="POST">
            <input type="hidden" id="delete_lab_id" name="lab_id">
            <div class="flex justify-end">
                <button type="button" id="cancelDeleteBtn" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors mr-2">
                    Cancel
                </button>
                <button type="submit" name="delete_lab" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    Delete Lab
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add Lab Form Toggle
        const addLabBtn = document.getElementById('addLabBtn');
        const addLabForm = document.getElementById('addLabForm');
        const cancelAddBtn = document.getElementById('cancelAddBtn');
        
        addLabBtn.addEventListener('click', function() {
            addLabForm.classList.toggle('hidden');
            addLabBtn.classList.toggle('hidden');
        });
        
        cancelAddBtn.addEventListener('click', function() {
            addLabForm.classList.add('hidden');
            addLabBtn.classList.remove('hidden');
        });
        
        // Edit Lab Modal
        const editLabModal = document.getElementById('editLabModal');
        const editBtns = document.querySelectorAll('.edit-lab-btn');
        const cancelEditBtn = document.getElementById('cancelEditBtn');
        
        editBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const labId = this.dataset.id;
                const labName = this.dataset.name;
                const totalPcs = this.dataset.pcs;
                const location = this.dataset.location;
                const status = this.dataset.status;
                
                document.getElementById('edit_lab_id').value = labId;
                document.getElementById('edit_lab_name').value = labName;
                document.getElementById('edit_total_pcs').value = totalPcs;
                document.getElementById('edit_location').value = location;
                document.getElementById('edit_status').value = status;
                
                editLabModal.classList.remove('hidden');
            });
        });
        
        cancelEditBtn.addEventListener('click', function() {
            editLabModal.classList.add('hidden');
        });
        
        // Delete Lab Modal
        const deleteLabModal = document.getElementById('deleteLabModal');
        const deleteBtns = document.querySelectorAll('.delete-lab-btn');
        const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
        
        deleteBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const labId = this.dataset.id;
                const labName = this.dataset.name;
                
                document.getElementById('delete_lab_id').value = labId;
                document.getElementById('delete_lab_name').textContent = labName;
                
                deleteLabModal.classList.remove('hidden');
            });
        });
        
        cancelDeleteBtn.addEventListener('click', function() {
            deleteLabModal.classList.add('hidden');
        });
        
        // Close modals when clicking outside
        window.addEventListener('click', function(e) {
            if (e.target === editLabModal) {
                editLabModal.classList.add('hidden');
            }
            if (e.target === deleteLabModal) {
                deleteLabModal.classList.add('hidden');
            }
        });
    });
</script>

<?php require_once '../shared/footer.php'; ?>
<script>
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