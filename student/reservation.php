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

<style> 
 .lab-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); 
  gap: 1rem;
}

.pc-item {
  @apply flex flex-col items-center justify-center p-2 border border-gray-300 rounded-lg cursor-pointer transition-all;
}

.pc-item.reserved {
  @apply bg-red-500 text-white opacity-60 cursor-not-allowed;
}

</style>
<div class="container mx-auto mt-10 px-4 sm:px-6 md:px-8 lg:px-10 ">
    <?php include '../shared/aside.php'; ?>
    <main class="my-4 ">
      <div class="max-w-[1200px] mx-auto">
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl p-6 sm:p-8 md:p-10 text-white mb-8 shadow-lg">
            <div class="flex items-center gap-4 ">
                <i class="fas fa-desktop text-4xl sm:text-5xl opacity-90"></i>
                <div >
                    <h1 class="text-3xl sm:text-4xl font-bold">
                        Lab Reservation
                    </h1>
                    <p class="text-indigo-100 mt-2">Reserve lab PCs for your study sessions</p>
                </div>
            </div>
        </div>
        
        <?php if (isset($successMessage)): ?>
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md" role="alert">
                <p class="font-medium">Success!</p>
                <p><?php echo $successMessage; ?></p>
            </div>
        <?php endif; ?>
        
        <?php if (isset($errorMessage)): ?>
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert">
                <p class="font-medium">Error!</p>
                <p><?php echo $errorMessage; ?></p>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
             <!-- Reservation Form -->
             <div class="bg-white rounded-xl shadow-sm border border-gray-200/50 overflow-hidden ">
                 <div class="border-b border-gray-200/50 px-6 py-4 flex items-center justify-start ">
                 
                        <div class="group flex items-center gap-3 w-full">
                            <i class="fas fa-calendar-alt text-indigo-600 group-hover:text-indigo-800 transition-all duration-300 ease-in-out "></i>

                            <h2 class="text-xl font-semibold ">Create Reservation</h2>
                           
                        </div>
                    </div>
                 <form method="POST" id="reservationForm" class="p-6">

                    <div class="space-y-6">
                        <!-- Select Reason -->
                        <div class="mb-2 ">
                            <label for="purpose" class="block text-sm font-medium text-gray-700 mb-1">Select Reason</label>
                            <select name="purpose" id="purpose" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                                <option value="" disabled selected>Select Reason</option>
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

                        <!-- Select Lab -->
                        <div class="mb-2">
                            <label for="lab_name" class="block text-sm font-medium text-gray-700 mb-1">Select Lab</label>
                            <select id="lab_name" name="lab_name" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                                <option value="" disabled selected>Select a Lab</option>
                                <?php while ($lab = $labsResult->fetch_assoc()): ?>
                                <option value="<?php echo $lab['lab_name']; ?>"><?php echo $lab['lab_name'] . ' - ' . $lab['location']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Reservation Date -->
                        <div class="mb-2">
                            <label for="reservation_date" class="block text-sm font-medium text-gray-700 mb-1">Reservation Date</label>
                            <input type="date" id="reservation_date" name="reservation_date" min="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
                        </div>
                        <!-- select time -->
                         <div class="mb-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select Time Slot</label>
                            <div class="flex flex-wrap gap-2">
                                    <div class="time-slot px-4 py-2 border border-gray-300 rounded-lg cursor-pointer transition-all hover:bg-indigo-100 hover:scale-105" data-value="8:00 AM - 10:00 AM" title="8:00 AM - 10:00 AM">8:00 AM - 10:00 AM</div>
                                    <div class="time-slot px-4 py-2 border border-gray-300 rounded-lg cursor-pointer transition-all hover:bg-indigo-100 hover:scale-105" data-value="10:00 AM - 12:00 PM" title="10:00 AM - 12:00 PM">10:00 AM - 12:00 PM</div>
                                    <div class="time-slot px-4 py-2 border border-gray-300 rounded-lg cursor-pointer transition-all hover:bg-indigo-100 hover:scale-105" data-value="1:00 PM - 3:00 PM" title="1:00 PM - 3:00 PM">1:00 PM - 3:00 PM</div>
                                    <div class="time-slot px-4 py-2 border border-gray-300 rounded-lg cursor-pointer transition-all hover:bg-indigo-100 hover:scale-105" data-value="3:00 PM - 5:00 PM" title="3:00 PM - 5:00 PM">3:00 PM - 5:00 PM</div>
                                    <div class="time-slot px-4 py-2 border border-gray-300 rounded-lg cursor-pointer transition-all hover:bg-indigo-100 hover:scale-105" data-value="5:00 PM - 7:00 PM" title="5:00 PM - 7:00 PM">5:00 PM - 7:00 PM</div>
                                </div>
                                <input type="hidden" id="time_slot" name="time_slot" required>
                        </div>
                        
                         <!-- Select PC -->
                        <div class="mb-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Select PC</label>
                            <div class="overflow-x-auto pb-4">
                                <div class="lab-grid" id="pcGrid"></div>
                            </div>
                            <input type="hidden" id="pc_number" name="pc_number" required>
                        

                         </div>
                        <!-- Submit Button -->
                        <div class="flex justify-end">
                                <button type="submit" name="submit_reservation" class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all duration-300 ease-in-out">
                                    <i class="fas fa-save mr-2"></i>Submit Reservation
                                </button>
                            </div> 
                        </form>
                    </div>
                </div>
                </div>
        
            <!-- Reservations List -->
            <div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200/50 overflow-hidden ">
                    <div class="border-b border-gray-200/50 px-6 py-4 flex items-center justify-between ">
                     <div class="flex items-center gap-3 w-full ">

                          <i class="fas fa-list text-indigo-600"></i>
                          <h2 class="text-xl font-semibold">My Reservations</h2>
                         
                     </div>

                    </div>
                    
                    <div class="p-6">
                        <?php if ($reservationsResult->num_rows > 0): ?>
                            <div class="space-y-4 h-full ">
                                <?php while ($reservation = $reservationsResult->fetch_assoc()): ?>                           
                                    <div class="group border border-gray-200 rounded-lg p-4 hover:shadow-xl transition-all duration-300 ease-in-out">
                                        <div class="flex justify-between items-start">
                                            <div class="flex-grow">
                                                <p class="font-medium text-gray-800"><?php echo $reservation['lab_name']; ?> - PC #<?php echo $reservation['pc_number']; ?></p>
                                                <p class="text-gray-600">
                                                    <i class="fas fa-calendar-day mr-1"></i> 
                                                    <?php echo date('F j, Y', strtotime($reservation['reservation_date'])); ?>
                                                </p>
                                                <p class="text-gray-600">
                                                    <i class="fas fa-clock mr-1"></i> 
                                                    <?php echo $reservation['time_slot']; ?>
                                                </p>
                                            </div>
                                            <div>
                                                <?php if ($reservation['status'] === 'pending'): ?>
                                                    <span class="animate-pulse inline-block px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">
                                                        Pending
                                                    </span>
                                                <?php elseif ($reservation['status'] === 'approved'): ?>
                                                    <span class="animate-pulse inline-block px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                                        Approved
                                                    </span>
                                                <?php elseif ($reservation['status'] === 'rejected'): ?>
                                                    <span class="animate-pulse inline-block px-3 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold">
                                                        Rejected
                                                    </span>
                                                <?php elseif ($reservation['status'] === 'completed'): ?>
                                                    <span class="animate-pulse inline-block px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-semibold">
                                                        Completed
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="flex justify-end mt-3">
                                            <?php if ($reservation['status'] === 'pending'): ?>
                                                <button class="group text-red-500 hover:text-red-700 text-sm" 
                                                       onclick="cancelReservation(<?php echo $reservation['reservation_id']; ?>)">
                                                    <i class="fas fa-times-circle mr-1"></i> Cancel
                                                </button>
                                            <?php endif; ?>
                                            <span class="text-gray-500 text-xs">
                                                Created on <?php echo date('M j, Y g:i A', strtotime($reservation['created_at'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8 ">
                                <i class="fas fa-calendar-xmark text-5xl text-gray-400 mb-3"></i>
                                <p class="text-gray-500">You don't have any reservations yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
       </div>
    </main>
</div>

<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<script>
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
