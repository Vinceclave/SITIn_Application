<?php
// filepath: d:\Xampp\htdocs\SITIn_Application\student\pc_selection.php
require_once '../config/config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Set content type to HTML
header('Content-Type: text/html');

// Check if lab_name, date, and time slot are provided
if (!isset($_GET['lab_name']) || empty($_GET['lab_name']) ||
    !isset($_GET['date']) || empty($_GET['date']) ||
    !isset($_GET['time_slot']) || empty($_GET['time_slot'])) {
    echo "<div class='text-center text-red-600'>Missing parameters</div>";
    exit;
}

$lab_name = filter_var($_GET['lab_name'], FILTER_SANITIZE_STRING);
$date = filter_var($_GET['date'], FILTER_SANITIZE_STRING);
$time_slot = filter_var($_GET['time_slot'], FILTER_SANITIZE_STRING);

// Validate date format (YYYY-MM-DD)
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    echo "<div class='text-center text-red-600'>Invalid date format</div>";
    exit;
}

// Get lab ID and information
$labQuery = "SELECT lab_id, total_pcs FROM labs WHERE lab_name = ?";
$labStmt = $conn->prepare($labQuery);
$labStmt->bind_param("s", $lab_name);
$labStmt->execute();
$labResult = $labStmt->get_result();

if ($labResult->num_rows === 0) {
    echo "<div class='text-center text-red-600'>Laboratory not found</div>";
    exit;
}

$labData = $labResult->fetch_assoc();
$lab_id = $labData['lab_id'];
$total_pcs = $labData['total_pcs'];

// Get all PCs in this lab
$pcsQuery = "SELECT p.pc_id, p.pc_number, p.status, p.notes 
             FROM pcs p 
             WHERE p.lab_id = ? 
             ORDER BY p.pc_number";
$pcsStmt = $conn->prepare($pcsQuery);
$pcsStmt->bind_param("i", $lab_id);
$pcsStmt->execute();
$pcsResult = $pcsStmt->get_result();

// Create array to store PC data
$pcs = [];
while ($pc = $pcsResult->fetch_assoc()) {
    $pcs[$pc['pc_number']] = [
        'pc_id' => $pc['pc_id'],
        'pc_number' => $pc['pc_number'],
        'status' => $pc['status'],
        'notes' => $pc['notes'],
        'available' => ($pc['status'] === 'available') ? true : false,
        'reserved' => false,
        'in_use' => false
    ];
}

// Get reservations for this lab, date, and time slot
$reservationsQuery = "SELECT pc_number, status FROM reservations 
                      WHERE lab_name = ? AND reservation_date = ? AND time_slot = ?";
$reservationsStmt = $conn->prepare($reservationsQuery);
$reservationsStmt->bind_param("sss", $lab_name, $date, $time_slot);
$reservationsStmt->execute();
$reservationsResult = $reservationsStmt->get_result();

// Mark reserved PCs as unavailable
while ($reservation = $reservationsResult->fetch_assoc()) {
    $pc_number = $reservation['pc_number'];
    if (isset($pcs[$pc_number])) {
        if ($reservation['status'] === 'approved' || $reservation['status'] === 'pending') {
            $pcs[$pc_number]['available'] = false;
            $pcs[$pc_number]['reserved'] = true;
        }
    }
}

// Get active sit-in sessions for this lab
$sitInQuery = "SELECT pc_number, idno, full_name FROM sit_in 
               WHERE lab = ? AND status = 1 AND out_time IS NULL";
$sitInStmt = $conn->prepare($sitInQuery);
$sitInStmt->bind_param("s", $lab_name);
$sitInStmt->execute();
$sitInResult = $sitInStmt->get_result();

// Mark PCs with active sessions as unavailable
while ($session = $sitInResult->fetch_assoc()) {
    $pc_number = $session['pc_number'];
    if (isset($pcs[$pc_number])) {
        $pcs[$pc_number]['available'] = false;
        $pcs[$pc_number]['in_use'] = true;
        $pcs[$pc_number]['user'] = [
            'idno' => $session['idno'],
            'name' => $session['full_name']
        ];
    }
}
?>

<div class="mt-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Select a PC</h3>
    
    <div class="grid grid-cols-5 md:grid-cols-10 gap-2 mb-4">
        <!-- Legend -->
        <div class="col-span-5 md:col-span-10 flex flex-wrap gap-4 mb-4">
            <div class="flex items-center">
                <div class="w-4 h-4 bg-green-500 rounded-sm mr-2"></div>
                <span class="text-sm">Available</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-red-500 rounded-sm mr-2"></div>
                <span class="text-sm">Unavailable/In Use</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-yellow-500 rounded-sm mr-2"></div>
                <span class="text-sm">Maintenance</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-blue-500 rounded-sm mr-2"></div>
                <span class="text-sm">Reserved</span>
            </div>
            <div class="flex items-center">
                <div class="w-4 h-4 bg-purple-500 rounded-sm mr-2"></div>
                <span class="text-sm">Selected</span>
            </div>
        </div>
        
        <!-- PC Grid -->
        <?php 
        // Calculate rows needed
        $rows = ceil($total_pcs / 10);
        $pc_index = 1;
        
        for ($row = 1; $row <= $rows; $row++) { 
            // Lab entrance or aisle
            if ($row == ceil($rows / 2)) {
                echo '<div class="col-span-5 md:col-span-10 h-8 my-2 bg-gray-200 flex items-center justify-center text-sm text-gray-600">Front entrance / Aisle</div>';
            }
            
            // Create row of PCs
            for ($i = 0; $i < 10 && $pc_index <= $total_pcs; $i++, $pc_index++) {
                // Get PC data
                $pc = isset($pcs[$pc_index]) ? $pcs[$pc_index] : [
                    'pc_id' => 0,
                    'pc_number' => $pc_index,
                    'status' => 'unavailable',
                    'available' => false,
                    'reserved' => false,
                    'in_use' => false
                ];
                
                // Determine background color based on status
                $bgColor = 'bg-gray-200 cursor-not-allowed'; // Default
                $icon = '🖥️';
                $title = "PC #$pc_index";
                
                if ($pc['available']) {
                    $bgColor = 'bg-green-500 hover:bg-green-600 cursor-pointer';
                } elseif ($pc['reserved']) {
                    $bgColor = 'bg-blue-500 cursor-not-allowed';
                    $title .= " (Reserved)";
                } elseif ($pc['in_use']) {
                    $bgColor = 'bg-red-500 cursor-not-allowed';
                    $title .= " (In Use)";
                    $icon = '👤';
                } elseif ($pc['status'] === 'maintenance') {
                    $bgColor = 'bg-yellow-500 cursor-not-allowed';
                    $title .= " (Maintenance)";
                    $icon = '🔧';
                }
                
                // Output PC button
                echo '<div class="pc-select-btn relative aspect-square flex items-center justify-center ' . $bgColor . ' text-white font-bold rounded-md shadow-sm text-xs md:text-sm" ';
                echo 'data-pc-number="' . $pc_index . '" ';
                echo 'data-available="' . ($pc['available'] ? 'true' : 'false') . '" ';
                echo 'title="' . $title . '">';
                echo $icon . '<span class="ml-1">' . $pc_index . '</span>';
                echo '</div>';
            }
        }
        ?>
    </div>
    
    <div class="mb-4">
        <label for="selected-pc" class="block text-sm font-medium text-gray-700">Selected PC</label>
        <input type="text" id="selected-pc" name="pc_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" readonly>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get all PC buttons
    const pcButtons = document.querySelectorAll('.pc-select-btn[data-available="true"]');
    const selectedPcInput = document.getElementById('selected-pc');
    
    // Add click event to available PCs
    pcButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove selected class from all buttons
            document.querySelectorAll('.pc-select-btn').forEach(btn => {
                btn.classList.remove('bg-purple-500', 'hover:bg-purple-600');
            });
            
            // Add selected class to this button
            this.classList.remove('bg-green-500', 'hover:bg-green-600');
            this.classList.add('bg-purple-500', 'hover:bg-purple-600');
            
            // Set the selected PC number
            const pcNumber = this.getAttribute('data-pc-number');
            selectedPcInput.value = pcNumber;
        });
    });
});
</script>
