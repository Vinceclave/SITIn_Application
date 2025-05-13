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

// Get student ID number
$idno = $user['idno'];

// Display success and error messages from session
if(isset($_SESSION['success'])): ?>
<script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.5/dist/notiflix-aio-3.2.5.min.js"></script>
<script>
    Notiflix.Notify.success("<?php echo addslashes($_SESSION['success']); ?>");
</script>
<?php unset($_SESSION['success']); endif; ?>

<?php if(isset($_SESSION['error'])): ?>
<script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.5/dist/notiflix-aio-3.2.5.min.js"></script>
<script>
    Notiflix.Notify.failure("<?php echo addslashes($_SESSION['error']); ?>");
</script>
<?php unset($_SESSION['error']); endif; ?>

<?php
// Check if labs table exists, and create it if it doesn't
$checkLabsTableQuery = "SHOW TABLES LIKE 'labs'";
$checkLabsTableResult = $conn->query($checkLabsTableQuery);

if ($checkLabsTableResult->num_rows == 0) {
    // Create labs table
    $createLabsTableQuery = "CREATE TABLE labs (
        lab_id INT AUTO_INCREMENT PRIMARY KEY,
        lab_name VARCHAR(50) NOT NULL,
        total_pcs INT NOT NULL DEFAULT 50,
        location VARCHAR(100) NOT NULL,
        status ENUM('available', 'unavailable') DEFAULT 'available'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($createLabsTableQuery) === TRUE) {
        // Insert sample lab data
        $insertLabsQuery = "INSERT INTO labs (lab_name, total_pcs, location, status) VALUES 
            ('524', 50, 'Main Building Floor 5', 'available'),
            ('526', 50, 'Main Building Floor 5', 'available'),
            ('528', 50, 'Main Building Floor 5', 'available'),
            ('530', 50, 'Main Building Floor 5', 'available'),
            ('542', 50, 'Main Building Floor 5', 'available'),
            ('544', 50, 'Main Building Floor 5', 'available'),
            ('517', 50, 'Main Building Floor 5', 'available')";
        $conn->query($insertLabsQuery);
    }
}

// Check if the reservations table exists
$checkReservationsTableQuery = "SHOW TABLES LIKE 'reservations'";
$checkReservationsTableResult = $conn->query($checkReservationsTableQuery);

if ($checkReservationsTableResult->num_rows == 0) {
    // Create reservations table
    $createReservationsTableQuery = "CREATE TABLE reservations (
        reservation_id INT AUTO_INCREMENT PRIMARY KEY,
        idno INT NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        lab_name VARCHAR(50) NOT NULL,
        pc_number INT NOT NULL,
        reservation_date DATE NOT NULL,
        time_slot VARCHAR(50) NOT NULL,
        status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (idno) REFERENCES users(idno) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    $conn->query($createReservationsTableQuery);
    
    // Add indexes for better performance
    $conn->query("CREATE INDEX idx_reservations_lab_date_slot ON reservations (lab_name, reservation_date, time_slot, status)");
    $conn->query("CREATE INDEX idx_reservations_user ON reservations (idno, reservation_date, status)");
}

// Process reservation submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reservation'])) {
    $lab_name = $_POST['lab_name'];
    $pc_number = $_POST['pc_number'];
    $reservation_date = $_POST['reservation_date'];
    $time_slot = $_POST['time_slot'];
    $purpose= $_POST['purpose'];
    $full_name = $user['firstname'] . ' ' . $user['lastname'];

    // Check if the student already has a pending reservation for the same date and time slot
    $checkExistingQuery = "SELECT * FROM reservations WHERE idno = ? AND reservation_date = ? AND time_slot = ? AND status = 'pending'";
    $checkStmt = $conn->prepare($checkExistingQuery);
    $checkStmt->bind_param("iss", $idno, $reservation_date, $time_slot);
    $checkStmt->execute();
    $existingResult = $checkStmt->get_result();    
    
    if ($existingResult->num_rows > 0) {
        $errorMessage = "You already have a pending reservation for this date and time slot.";
    } else {
        // Check if the PC is already reserved for the selected date and time slot
        $checkPcQuery = "SELECT * FROM reservations WHERE lab_name = ? AND pc_number = ? AND reservation_date = ? AND time_slot = ? AND status IN ('pending', 'approved')";
         $checkPcStmt = $conn->prepare($checkPcQuery);
         $checkPcStmt->bind_param("siss", $lab_name, $pc_number, $reservation_date, $time_slot);
         $checkPcStmt->execute();
         $pcResult = $checkPcStmt->get_result();
        
        if ($pcResult->num_rows > 0) {
            $errorMessage = "This PC is already reserved for the selected date and time slot.";        } else {
            // Insert reservation
            $insertQuery = "INSERT INTO reservations (idno, full_name, lab_name, pc_number, reservation_date, time_slot, purpose) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param("ississs", $idno, $full_name, $lab_name, $pc_number, $reservation_date, $time_slot, $purpose);

            if ($insertStmt->execute()) {
                $successMessage = "Reservation submitted successfully!";
            } else {
                $errorMessage = "Error: " . $insertStmt->error;
            }
        }
    }
}

// Fetch labs for dropdown
$labsQuery = "SELECT * FROM labs WHERE status = 'available'";
$labsResult = $conn->query($labsQuery);

// Fetch student's reservations
$reservationsQuery = "SELECT * FROM reservations WHERE idno = ? ORDER BY reservation_date DESC, created_at DESC";
$reservationsStmt = $conn->prepare($reservationsQuery);
$reservationsStmt->bind_param("i", $idno);
$reservationsStmt->execute();
$reservationsResult = $reservationsStmt->get_result();
?>

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
                        <h1 class="text-3xl font-bold text-gray-800">Lab Reservation</h1>
                        <p class="text-lg text-gray-600">Welcome, <span class="font-medium text-indigo-600"><?php echo htmlspecialchars($user['username']); ?></span>!</p>
                    </div>
                </div>
            </div>

            <!-- Stats Section -->
            <?php
            // Get total reservations for this student
            $totalReservationsQuery = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
                FROM reservations WHERE idno = ?";
            $statsStmt = $conn->prepare($totalReservationsQuery);
            $statsStmt->bind_param("i", $idno);
            $statsStmt->execute();
            $stats = $statsStmt->get_result()->fetch_assoc();
            ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 text-white p-6 rounded-xl shadow-md hover:shadow-xl transform hover:scale-105 transition duration-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-indigo-100">Total Reservations</h2>
                        <div class="bg-white/20 p-2 rounded-lg">
                            <i class="fas fa-calendar-check text-xl"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold mt-2"><?php echo number_format($stats['total']); ?></p>
                    <p class="text-indigo-200 text-sm mt-1">All-time reservations</p>
                </div>
                
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 text-white p-6 rounded-xl shadow-md hover:shadow-xl transform hover:scale-105 transition duration-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-emerald-100">Approved Sessions</h2>
                        <div class="bg-white/20 p-2 rounded-lg">
                            <i class="fas fa-check-circle text-xl"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold mt-2"><?php echo number_format($stats['approved']); ?></p>
                    <p class="text-emerald-200 text-sm mt-1">Approved reservations</p>
                </div>
                
                <div class="bg-gradient-to-br from-amber-500 to-amber-600 text-white p-6 rounded-xl shadow-md hover:shadow-xl transform hover:scale-105 transition duration-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-amber-100">Pending Requests</h2>
                        <div class="bg-white/20 p-2 rounded-lg">
                            <i class="fas fa-clock text-xl"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold mt-2"><?php echo number_format($stats['pending']); ?></p>
                    <p class="text-amber-200 text-sm mt-1">Awaiting approval</p>
                </div>
            </div>

            <!-- Actions Section -->
            <div class="mb-8">
                <button id="openModal" 
                    class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all duration-300 ease-in-out shadow-md hover:shadow-xl transform hover:scale-105">
                    <i class="fas fa-plus mr-2"></i>
                    Create New Reservation
                </button>
            </div>

            <!-- Reservations List -->
            <div class="bg-white rounded-xl shadow-md border border-gray-100">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center space-x-3">
                        <div class="bg-indigo-100 p-2 rounded-lg">
                            <i class="fas fa-list text-indigo-600"></i>
                        </div>
                        <h2 class="text-xl font-bold text-gray-800">My Reservations</h2>
                    </div>
                </div>
                
                <div class="p-6">
                    <?php if ($reservationsResult->num_rows > 0): ?>
                        <div class="space-y-4">
                            <?php while ($reservation = $reservationsResult->fetch_assoc()): ?>
                                <div class="group bg-gray-50 border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-all duration-300 ease-in-out">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-grow">
                                            <div class="flex items-center space-x-3">
                                                <div class="bg-indigo-100 p-2 rounded-full">
                                                    <i class="fas fa-desktop text-indigo-600"></i>
                                                </div>
                                                <div>
                                                    <p class="font-medium text-gray-800">Lab <?php echo $reservation['lab_name']; ?> - PC #<?php echo $reservation['pc_number']; ?></p>
                                                    <div class="flex items-center space-x-4 mt-2 text-sm text-gray-600">
                                                        <span><i class="fas fa-calendar-day mr-1"></i> <?php echo date('F j, Y', strtotime($reservation['reservation_date'])); ?></span>
                                                        <span><i class="fas fa-clock mr-1"></i> <?php echo $reservation['time_slot']; ?></span>
                                                    </div>
                                                    <p class="mt-2 text-sm text-gray-600">
                                                        <i class="fas fa-file-alt mr-1"></i> Purpose: <?php echo $reservation['purpose']; ?>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <?php if ($reservation['status'] === 'pending'): ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    <i class="fas fa-clock mr-1"></i> Pending
                                                </span>
                                            <?php elseif ($reservation['status'] === 'approved'): ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-check-circle mr-1"></i> Approved
                                                </span>
                                            <?php elseif ($reservation['status'] === 'rejected'): ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    <i class="fas fa-times-circle mr-1"></i> Rejected
                                                </span>
                                            <?php elseif ($reservation['status'] === 'completed'): ?>
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    <i class="fas fa-check mr-1"></i> Completed
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex justify-between items-center mt-4 pt-3 border-t border-gray-200">
                                        <span class="text-gray-500 text-sm">
                                            <i class="far fa-clock mr-1"></i>
                                            Created <?php echo date('M j, Y g:i A', strtotime($reservation['created_at'])); ?>
                                        </span>
                                        <?php if ($reservation['status'] === 'pending'): ?>
                                            <button onclick="cancelReservation(<?php echo $reservation['reservation_id']; ?>)"
                                                class="px-3 py-1 text-sm text-red-600 hover:text-red-800 transition-colors">
                                                <i class="fas fa-times-circle mr-1"></i>
                                                Cancel Reservation
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <div class="bg-gray-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-calendar-xmark text-3xl text-gray-400"></i>
                            </div>
                            <h3 class="text-xl font-medium text-gray-600 mb-2">No Reservations Yet</h3>
                            <p class="text-gray-500">Create your first lab reservation to get started.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Modal -->
<div id="reservationModal" class="fixed z-50 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true"></div>

        <!-- Modal Panel -->
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-white">
                <!-- Modal Header -->
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="bg-indigo-100 p-2 rounded-lg">
                                <i class="fas fa-calendar-plus text-indigo-600"></i>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900" id="modal-title">Create New Reservation</h3>
                        </div>
                        <button type="button" id="closeModal" class="text-gray-400 hover:text-gray-500">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="px-6 py-4">
                    <form method="POST" id="reservationForm" class="space-y-6">
                        <!-- Purpose Selection -->
                        <div>
                            <label for="purpose" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-tasks mr-2"></i>Purpose
                            </label>
                            <select name="purpose" id="purpose" 
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="" disabled selected>Select your purpose</option>
                                <option value="C programming">C programming</option>
                                <option value="C# programming">C# programming</option>
                                <option value="Java programming">Java programming</option>
                                <option value="PHP programming">PHP programming</option>
                                <option value="Database">Database</option>
                                <option value="Digital Logic & Design">Digital Logic & Design</option>
                                <option value="Embedded Systems & IoT">Embedded Systems & IoT</option>
                                <option value="Python Programming">Python Programming</option>
                                <option value="Systems Integration & Architecture">Systems Integration & Architecture</option>
                                <option value="Computer Application">Computer Application</option>
                                <option value="Web Design & Development">Web Design & Development</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <!-- Lab Selection -->
                        <div>
                            <label for="lab_name" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-building mr-2"></i>Select Lab
                            </label>
                            <select id="lab_name" name="lab_name" 
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                <option value="" disabled selected>Select a laboratory</option>
                                <?php while ($lab = $labsResult->fetch_assoc()): ?>
                                    <option value="<?php echo $lab['lab_name']; ?>">
                                        <?php echo $lab['lab_name'] . ' - ' . $lab['location']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Date Selection -->
                        <div>
                            <label for="reservation_date" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-calendar mr-2"></i>Date
                            </label>
                            <input type="date" id="reservation_date" name="reservation_date" 
                                min="<?php echo date('Y-m-d'); ?>" 
                                max="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" 
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" 
                                required>
                        </div>

                        <!-- Time Slot Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-clock mr-2"></i>Time Slot
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                <div class="time-slot px-4 py-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-indigo-50 transition-colors text-center" data-value="8:00 AM - 10:00 AM">8:00 AM - 10:00 AM</div>
                                <div class="time-slot px-4 py-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-indigo-50 transition-colors text-center" data-value="10:00 AM - 12:00 PM">10:00 AM - 12:00 PM</div>
                                <div class="time-slot px-4 py-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-indigo-50 transition-colors text-center" data-value="1:00 PM - 3:00 PM">1:00 PM - 3:00 PM</div>
                                <div class="time-slot px-4 py-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-indigo-50 transition-colors text-center" data-value="3:00 PM - 5:00 PM">3:00 PM - 5:00 PM</div>
                                <div class="time-slot px-4 py-3 border border-gray-300 rounded-lg cursor-pointer hover:bg-indigo-50 transition-colors text-center" data-value="5:00 PM - 7:00 PM">5:00 PM - 7:00 PM</div>
                            </div>
                            <input type="hidden" id="time_slot" name="time_slot" required>
                        </div>

                        <!-- PC Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-desktop mr-2"></i>Select PC
                            </label>
                            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                <div class="overflow-x-auto">
                                    <div class="lab-grid" id="pcGrid">
                                        <!-- PCs will be loaded here -->
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="pc_number" name="pc_number" required>
                        </div>
                    </form>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="document.getElementById('reservationModal').classList.add('hidden')"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" form="reservationForm" name="submit_reservation"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-lg hover:bg-indigo-700">
                            <i class="fas fa-save mr-2"></i>Submit Reservation
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* PC Grid */
.lab-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
    gap: 1rem;
    padding: 1rem;
}

.pc-item {
    text-align: center;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.pc-item:not(.reserved):hover {
    transform: translateY(-2px);
}

.pc-item.reserved {
    background-color: #fee2e2;
    border-color: #fecaca;
    color: #991b1b;
    cursor: not-allowed;
    opacity: 0.7;
}

.pc-item.selected {
    background-color: #e0e7ff;
    border-color: #818cf8;
    color: #4f46e5;
}

/* Time slot styles */
.time-slot.selected {
    @apply bg-indigo-600 text-white ring-2 ring-indigo-300;
}
</style>

<script>
    // modal
    const modal = document.getElementById('reservationModal');
    const openModalButton = document.getElementById('openModal');
    const closeModalButton = document.getElementById('closeModal');

    // open modal
    openModalButton.addEventListener('click', () => {
        modal.classList.remove('hidden');
    });

    // close modal
    closeModalButton.addEventListener('click', () => {
        modal.classList.add('hidden');
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Generate PC Grid
        const pcGrid = document.getElementById('pcGrid');
        const labSelect = document.getElementById('lab_name');
        const reservationDateInput = document.getElementById('reservation_date');
        const timeSlots = document.querySelectorAll('.time-slot');
        const pcNumberInput = document.getElementById('pc_number');
        const timeSlotInput = document.getElementById('time_slot');
        
        // Function to generate the PC grid based on the selected lab
        function generatePcGrid() {
            const selectedLab = labSelect.value;
             const selectedDate = reservationDateInput.value;
            const selectedTimeSlot = timeSlotInput.value;
            
           if (!selectedLab || !selectedDate || !selectedTimeSlot) return;
            
            // Clear existing grid
            pcGrid.innerHTML = '';
            
            // Create loading indicator
            const loadingDiv = document.createElement('div');
            loadingDiv.className = 'col-span-full text-center py-4';
            loadingDiv.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>';
            pcGrid.appendChild(loadingDiv);
            
            // Fetch reserved PCs from the server using AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'fetch_reserved_pcs.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    // Remove loading indicator
                    pcGrid.removeChild(loadingDiv);
                    
                    try {
                        const reservedPCs = JSON.parse(xhr.responseText);
                        
                       // Generate 50 PC items
                        for (let i = 1; i <= 50; i++) {
                            const pcItem = document.createElement('div');
                            pcItem.className = 'pc-item pc-item px-3 py-2 border border-gray-300 rounded-lg cursor-pointer transition-all hover:bg-indigo-100 hover:scale-105';
                            pcItem.dataset.pcNumber = i;
                            pcItem.innerHTML = `<i class="fas fa-desktop mb-1"></i><br>PC ${i}`;
                            
                            // Check if this PC is reserved
                             if (reservedPCs.includes(i)) {
                                pcItem.classList.add('reserved');
                                pcItem.title = 'This PC is already reserved';
                            } else {
                                pcItem.addEventListener('click', function() {
                                 // Remove selected class from all PC items
                                  document.querySelectorAll('.pc-item.selected').forEach(function(item) {
                                      item.classList.remove('selected', 'bg-indigo-200', 'ring-2', 'ring-indigo-400');
                                  });

                                  // Add selected class to this PC item
                                  this.classList.add('selected', 'bg-indigo-200', 'ring-2', 'ring-indigo-400');

                                  // Update hidden input value
                                  pcNumberInput.value = this.dataset.pcNumber;

                                  // Update hidden input value
                                  pcNumberInput.value = this.dataset.pcNumber;
                                });
                            }
                            
                            pcGrid.appendChild(pcItem);
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                        pcGrid.innerHTML = '<div class="col-span-full text-center py-4 text-red-500">Error loading PCs. Please try again.</div>';
                    }
                } else {
                    pcGrid.innerHTML = '<div class="col-span-full text-center py-4 text-red-500">Error loading PCs. Please try again.</div>';
                }
            };
            
            xhr.onerror = function() {
                pcGrid.innerHTML = '<div class="col-span-full text-center py-4 text-red-500">Network error. Please try again.</div>';
            };
            
            xhr.send(`lab_name=${encodeURIComponent(selectedLab)}&reservation_date=${encodeURIComponent(selectedDate)}&time_slot=${encodeURIComponent(selectedTimeSlot)}`);
        }
        
        // Time slot selection
        timeSlots.forEach(function(slot) {
            slot.addEventListener('click', function() {
              // Remove selected class from all time slots
              timeSlots.forEach(function(s) {
                s.classList.remove('selected', 'bg-indigo-600', 'text-white', 'ring-2', 'ring-indigo-300');
              });

              // Add selected class to this time slot
              this.classList.add('selected', 'bg-indigo-600', 'text-white', 'ring-2', 'ring-indigo-300');

             // Add selected class to this time slot
               this.classList.add('selected');
               this.classList.add('bg-indigo-600','text-white','ring-2','ring-indigo-300')
               
              // Update hidden input value
              timeSlotInput.value = this.dataset.value;

                // Update hidden input value
                timeSlotInput.value = this.dataset.value;
                
                // Regenerate PC grid if all required fields are filled
                if (labSelect.value && reservationDateInput.value) {
                   generatePcGrid();

                }
            });
        });

        // Event listeners for form changes
        labSelect.addEventListener('change', function() {
            if (this.value && reservationDateInput.value && timeSlotInput.value) {
                generatePcGrid();
            }
        });
        
        reservationDateInput.addEventListener('change', function() {
            if (this.value && labSelect.value && timeSlotInput.value) {
                generatePcGrid();
            }
        });
        
        // Form validation
        document.getElementById('reservationForm').addEventListener('submit', function(e) {
             if (!labSelect.value || !reservationDateInput.value || !timeSlotInput.value || !pcNumberInput.value) {
                 e.preventDefault();

                 if (!labSelect.value) alert('Please select a lab.');
                 else if (!reservationDateInput.value) alert('Please select a reservation date.');
                 else if (!timeSlotInput.value) alert("Please select a time slot.");
                 else if (!pcNumberInput.value) alert("Please select a PC.");

                 
            }
        });
        
        // Function to cancel reservation
        window.cancelReservation = function(reservationId) {
            if (confirm('Are you sure you want to cancel this reservation?')) {
                window.location.href = `cancel_reservation.php?id=${reservationId}`;
            }
        };
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php require_once '../shared/footer.php'; ?>
