<?php
// filepath: d:\Xampp\htdocs\SITIn_Application\admin\pc_management.php
require_once '../config/config.php';
session_start();

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit;
}

// Check if the pcs table has the last_used column
$check_column = $conn->query("SHOW COLUMNS FROM `pcs` LIKE 'last_used'");
$last_used_exists = $check_column->num_rows > 0;

// Check if our stored procedure exists
$check_proc = $conn->query("SHOW PROCEDURE STATUS WHERE Db = DATABASE() AND Name = 'update_sitin_pc_id'");
$proc_exists = $check_proc->num_rows > 0;

// If either check fails, run the fix script
if (!$last_used_exists || !$proc_exists) {
    header("Location: fix_pc_management.php");
    exit;
}

require_once '../shared/header.php';

// Handle form submission for updating PC status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // Update PC status
    if ($action === 'update_pc_status' && isset($_POST['pc_id']) && isset($_POST['status'])) {
        $pc_id = filter_var($_POST['pc_id'], FILTER_VALIDATE_INT);
        $status = filter_var($_POST['status'], FILTER_SANITIZE_STRING);
        $notes = isset($_POST['notes']) ? filter_var($_POST['notes'], FILTER_SANITIZE_STRING) : null;
        
        if ($pc_id && in_array($status, ['available', 'unavailable', 'maintenance'])) {
            $update_stmt = $conn->prepare("UPDATE pcs SET status = ?, notes = ? WHERE pc_id = ?");
            $update_stmt->bind_param("ssi", $status, $notes, $pc_id);
            
            if ($update_stmt->execute()) {
                $success_message = "PC status updated successfully!";
            } else {
                $error_message = "Failed to update PC status: " . $conn->error;
            }
        } else {
            $error_message = "Invalid parameters provided.";
        }
    }
    
    // End active session on PC
    if ($action === 'end_session' && isset($_POST['pc_id'])) {
        $pc_id = filter_var($_POST['pc_id'], FILTER_VALIDATE_INT);
        
        if ($pc_id) {
            // First find the active sit_in session for this PC
            $session_stmt = $conn->prepare("SELECT sit_in_id, idno FROM sit_in WHERE pc_id = ? AND status = 1 AND out_time IS NULL");
            $session_stmt->bind_param("i", $pc_id);
            $session_stmt->execute();
            $session_result = $session_stmt->get_result();
            
            if ($session_result->num_rows > 0) {
                $session = $session_result->fetch_assoc();
                $sit_in_id = $session['sit_in_id'];
                $idno = $session['idno'];
                
                // Begin transaction
                $conn->begin_transaction();
                
                try {
                    // End the sit_in session
                    $end_stmt = $conn->prepare("UPDATE sit_in SET out_time = NOW(), status = 0 WHERE sit_in_id = ?");
                    $end_stmt->bind_param("i", $sit_in_id);
                    $end_stmt->execute();
                    
                    // Update PC status to available
                    $pc_stmt = $conn->prepare("UPDATE pcs SET status = 'available' WHERE pc_id = ?");
                    $pc_stmt->bind_param("i", $pc_id);
                    $pc_stmt->execute();
                    
                    // Deduct session from student_session table
                    $deduct_stmt = $conn->prepare("UPDATE student_session SET session = session - 1 WHERE idno = ? AND session > 0");
                    $deduct_stmt->bind_param("i", $idno);
                    $deduct_stmt->execute();
                    
                    // Commit transaction
                    $conn->commit();
                    $success_message = "Session ended successfully!";
                } catch (Exception $e) {
                    // Rollback transaction on error
                    $conn->rollback();
                    $error_message = "Error ending session: " . $e->getMessage();
                }
            } else {
                $error_message = "No active session found for this PC.";
            }
        } else {
            $error_message = "Invalid PC ID provided.";
        }
    }
}

// Fetch labs data
$labs_query = "SELECT * FROM labs ORDER BY lab_name";
$labs_result = $conn->query($labs_query);
$labs = [];

if ($labs_result->num_rows > 0) {
    while ($lab = $labs_result->fetch_assoc()) {
        $labs[] = $lab;
    }
}
?>

<?php if(isset($success_message)): ?>
<script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.5/dist/notiflix-aio-3.2.5.min.js"></script>
<script>
    Notiflix.Notify.success("<?php echo addslashes($success_message); ?>");
</script>
<?php endif; ?>

<?php if(isset($error_message)): ?>
<script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.5/dist/notiflix-aio-3.2.5.min.js"></script>
<script>
    Notiflix.Notify.failure("<?php echo addslashes($error_message); ?>");
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
                        <i class="fas fa-desktop text-2xl text-indigo-600"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">PC Management</h1>
                        <p class="text-lg text-gray-600">Monitor and manage computer availability across labs</p>
                    </div>            </div>
            </div>

            <!-- Lab Selection -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6 border border-gray-100">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Select Laboratory</h2>
                <div class="mb-4">
                    <label for="lab-select" class="block text-gray-700 font-medium mb-2">Choose a lab to manage:</label>
                    <select id="lab-select" class="w-full p-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                        <option value="">-- Select a laboratory --</option>
                        <?php foreach ($labs as $lab): ?>
                            <option value="<?php echo $lab['lab_id']; ?>"><?php echo $lab['lab_name'] . ' (' . $lab['location'] . ')'; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>            <!-- PC Grid -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6 border border-gray-100">
                <h2 class="text-xl font-bold text-gray-800 mb-4">PC Status Overview</h2>
                <div id="pc-grid" class="grid grid-cols-2 md:grid-cols-5 lg:grid-cols-10 gap-4">
                    <!-- PC cards will be loaded here dynamically -->
                    <div class="text-center p-4 text-gray-500">
                        <i class="fas fa-info-circle text-xl mb-2"></i>
                        <p>Please select a laboratory to view PCs</p>
                    </div>
                </div>
            </div>        <!-- Active Sessions -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6 border border-gray-100">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Active Sessions</h2>
            <div class="overflow-x-auto">
                <table id="active-sessions-table" class="min-w-full bg-white">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PC #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lab</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Time</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purpose</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="active-sessions-body" class="divide-y divide-gray-200">
                        <!-- Active sessions will be loaded here dynamically -->
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">No active sessions found</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>    <!-- PC Details Modal -->
    <div id="pc-modal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
        <div class="fixed inset-0 bg-black opacity-50"></div>
        <div class="bg-white rounded-xl p-8 max-w-2xl w-full z-10 relative">
            <button id="close-modal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
            <h2 id="modal-title" class="text-2xl font-bold text-gray-800 mb-4">PC Details</h2>
            <div id="modal-content">
                <!-- PC details will be populated here -->
            </div>
            <div class="mt-6 flex justify-end space-x-4">
                <button id="modal-cancel" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition-colors">
                    Cancel
                </button>
                <button id="modal-save" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transition-colors">
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const labSelect = document.getElementById('lab-select');
    const pcGrid = document.getElementById('pc-grid');
    const pcModal = document.getElementById('pc-modal');
    const closeModal = document.getElementById('close-modal');
    const modalCancel = document.getElementById('modal-cancel');
    const modalTitle = document.getElementById('modal-title');
    const modalContent = document.getElementById('modal-content');
    const modalSave = document.getElementById('modal-save');
    
    // Function to load PCs for selected lab
    labSelect.addEventListener('change', function() {
        const labId = this.value;
        if (!labId) {
            pcGrid.innerHTML = `
                <div class="text-center p-4 text-gray-500">
                    <i class="fas fa-info-circle text-xl mb-2"></i>
                    <p>Please select a laboratory to view PCs</p>
                </div>
            `;
            return;
        }
        
        fetch(`fetch_pcs.php?lab_id=${labId}`)
            .then(response => response.json())
            .then(data => {
                if (data.pcs && data.pcs.length > 0) {
                    let pcCards = '';
                    data.pcs.forEach(pc => {
                        let statusClass = 'bg-green-100 text-green-800'; // Default for available
                        let statusIcon = 'fa-check-circle';
                        
                        if (pc.status === 'unavailable') {
                            statusClass = 'bg-red-100 text-red-800';
                            statusIcon = 'fa-times-circle';
                        } else if (pc.status === 'maintenance') {
                            statusClass = 'bg-yellow-100 text-yellow-800';
                            statusIcon = 'fa-exclamation-triangle';
                        }
                        
                        pcCards += `
                            <div class="pc-card cursor-pointer rounded-lg border p-4 ${statusClass} hover:shadow-md transition-all" data-pc-id="${pc.pc_id}">
                                <div class="text-center">
                                    <i class="fas fa-desktop text-3xl mb-2"></i>
                                    <h3 class="font-bold">PC #${pc.pc_number}</h3>
                                    <div class="mt-2 flex items-center justify-center">
                                        <i class="fas ${statusIcon} mr-1"></i>
                                        <span>${pc.status}</span>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    
                    pcGrid.innerHTML = pcCards;
                    
                    // Add click event listeners to all PC cards
                    document.querySelectorAll('.pc-card').forEach(card => {
                        card.addEventListener('click', function() {
                            const pcId = this.getAttribute('data-pc-id');
                            showPCDetails(pcId);
                        });
                    });
                    
                    // Also load active sessions for this lab
                    loadActiveSessions(labId);
                } else {
                    pcGrid.innerHTML = `
                        <div class="text-center p-4 text-gray-500">
                            <i class="fas fa-exclamation-circle text-xl mb-2"></i>
                            <p>No PCs found for this laboratory</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error fetching PCs:', error);
                pcGrid.innerHTML = `
                    <div class="text-center p-4 text-red-500">
                        <i class="fas fa-exclamation-circle text-xl mb-2"></i>
                        <p>Error loading PC data</p>
                    </div>
                `;
            });
    });
    
    // Function to load active sessions for a lab
    function loadActiveSessions(labId) {
        const sessionsTable = document.getElementById('active-sessions-body');
        
        fetch(`fetch_active_sessions.php?lab_id=${labId}`)
            .then(response => response.json())
            .then(data => {
                if (data.sessions && data.sessions.length > 0) {
                    let sessionRows = '';
                    
                    data.sessions.forEach(session => {
                        // Format the date/time
                        const startTime = new Date(session.in_time);
                        const formattedTime = startTime.toLocaleString();
                        
                        sessionRows += `
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">${session.pc_number}</td>
                                <td class="px-6 py-4 whitespace-nowrap">${session.lab_name}</td>
                                <td class="px-6 py-4 whitespace-nowrap">${session.idno}</td>
                                <td class="px-6 py-4 whitespace-nowrap">${session.full_name}</td>
                                <td class="px-6 py-4 whitespace-nowrap">${formattedTime}</td>
                                <td class="px-6 py-4 whitespace-nowrap">${session.purpose || session.reason}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button class="end-session-btn bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded text-sm" 
                                            data-pc-id="${session.pc_id}" data-sit-in-id="${session.sit_in_id}">
                                        End Session
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    
                    sessionsTable.innerHTML = sessionRows;
                    
                    // Add event listeners to end session buttons
                    document.querySelectorAll('.end-session-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const pcId = this.getAttribute('data-pc-id');
                            endSession(pcId);
                        });
                    });
                } else {
                    sessionsTable.innerHTML = `
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">No active sessions found</td>
                        </tr>
                    `;
                }
            })
            .catch(error => {
                console.error('Error fetching sessions:', error);
                sessionsTable.innerHTML = `
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-red-500">Error loading active sessions</td>
                    </tr>
                `;
            });
    }
    
    // Function to show PC details in modal
    function showPCDetails(pcId) {
        fetch(`get_pc_details.php?pc_id=${pcId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.pc) {
                    const pc = data.pc;
                    modalTitle.textContent = `PC #${pc.pc_number} (${pc.lab_name})`;
                    
                    // Create form content for the modal
                    modalContent.innerHTML = `
                        <form id="pc-form">
                            <input type="hidden" name="pc_id" value="${pc.pc_id}">
                            <input type="hidden" name="action" value="update_pc_status">
                            
                            <div class="mb-4">
                                <label class="block text-gray-700 font-bold mb-2" for="pc-status">
                                    Status
                                </label>
                                <select id="pc-status" name="status" class="w-full p-2 border border-gray-300 rounded">
                                    <option value="available" ${pc.status === 'available' ? 'selected' : ''}>Available</option>
                                    <option value="unavailable" ${pc.status === 'unavailable' ? 'selected' : ''}>Unavailable</option>
                                    <option value="maintenance" ${pc.status === 'maintenance' ? 'selected' : ''}>Maintenance</option>
                                </select>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-gray-700 font-bold mb-2" for="pc-notes">
                                    Notes
                                </label>
                                <textarea id="pc-notes" name="notes" rows="3" class="w-full p-2 border border-gray-300 rounded resize-none">${pc.notes || ''}</textarea>
                            </div>
                              <div class="bg-gray-100 p-4 rounded mb-4">
                                <h3 class="font-bold text-gray-700 mb-2">PC Information</h3>
                                <p><strong>Last Used:</strong> ${pc.last_used ? new Date(pc.last_used).toLocaleString() : 'Never'}</p>
                                ${pc.current_user ? `
                                    <div class="mt-2 p-3 bg-blue-50 rounded border border-blue-200">
                                        <h4 class="font-bold text-blue-800">Current User</h4>
                                        <p><strong>ID:</strong> ${pc.current_user.idno}</p>
                                        <p><strong>Name:</strong> ${pc.current_user.full_name}</p>
                                        <p><strong>Started at:</strong> ${new Date(pc.current_user.in_time).toLocaleString()}</p>
                                        ${pc.current_user.reason ? `<p><strong>Reason:</strong> ${pc.current_user.reason}</p>` : ''}
                                        ${pc.current_user.purpose ? `<p><strong>Purpose:</strong> ${pc.current_user.purpose}</p>` : ''}
                                    </div>
                                ` : ''}
                            </div>
                        </form>
                        
                        ${pc.current_user ? `
                            <div class="mt-4">
                                <button id="end-session-btn" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded w-full" data-pc-id="${pc.pc_id}">
                                    End Current Session
                                </button>
                            </div>
                        ` : ''}
                    `;
                    
                    // Show the modal
                    pcModal.classList.remove('hidden');
                    
                    // Add event listener to end session button if present
                    const endSessionBtn = document.getElementById('end-session-btn');
                    if (endSessionBtn) {
                        endSessionBtn.addEventListener('click', function() {
                            const pcId = this.getAttribute('data-pc-id');
                            endSession(pcId);
                        });
                    }
                } else {
                    alert('Error: Could not load PC details');
                }
            })
            .catch(error => {
                console.error('Error fetching PC details:', error);
                alert('Error: Could not load PC details');
            });
    }
    
    // Function to end a session
    function endSession(pcId) {
        if (confirm('Are you sure you want to end this session?')) {
            const formData = new FormData();
            formData.append('action', 'end_session');
            formData.append('pc_id', pcId);
            
            fetch('pc_management.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(html => {
                // Reload the page to show updated status
                window.location.reload();
            })
            .catch(error => {
                console.error('Error ending session:', error);
                alert('Error: Failed to end session');
            });
        }
    }
    
    // Event listener for modal save button
    modalSave.addEventListener('click', function() {
        const form = document.getElementById('pc-form');
        const formData = new FormData(form);
        
        fetch('pc_management.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            // Reload the page to show updated status
            window.location.reload();
        })
        .catch(error => {
            console.error('Error updating PC:', error);
            alert('Error: Failed to update PC status');
        });
    });
    
    // Close modal when clicking close button or cancel
    closeModal.addEventListener('click', function() {
        pcModal.classList.add('hidden');
    });
    
    modalCancel.addEventListener('click', function() {
        pcModal.classList.add('hidden');
    });
    
    // Close modal when clicking outside of it
    pcModal.addEventListener('click', function(e) {
        if (e.target === pcModal) {
            pcModal.classList.add('hidden');
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
