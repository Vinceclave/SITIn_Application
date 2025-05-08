<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/config.php';
require_once '../shared/header.php';

// Process reservation status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $reservation_id = $_POST['reservation_id'];
    $new_status = $_POST['new_status'];
    
    // Begin transaction
    $conn->begin_transaction();

    $updateQuery = "UPDATE reservations SET status = ? WHERE reservation_id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("si", $new_status, $reservation_id);
    
    if ($updateStmt->execute()) {
        // If the status is approved, insert data into sit_in table
        if ($new_status == 'approved') {
            $selectReservation = "SELECT idno, CONCAT(firstname, ' ', lastname) as full_name, lab_name, COALESCE(purpose, 'Default Reason') AS purpose, time_slot, reservation_date FROM reservations r JOIN users u ON r.idno = u.idno WHERE reservation_id = ?";
            $selectStmt = $conn->prepare($selectReservation);
            $selectStmt->bind_param("i", $reservation_id);
            $selectStmt->execute();
            $reservationResult = $selectStmt->get_result()->fetch_assoc();

             // Adjust the values to match the sit_in table structure
            $insertSitIn = "INSERT INTO sit_in (idno, full_name, lab, reason, in_time, sit_date, status) VALUES (?, ?, ?, ?, ?, ?, 1)";            
            $insertStmt = $conn->prepare($insertSitIn);
            $insertStmt->bind_param("ssssss", $reservationResult['idno'], $reservationResult['full_name'], $reservationResult['lab_name'], $reservationResult['purpose'], $reservationResult['time_slot'], $reservationResult['reservation_date']);

            if (!$insertStmt->execute()) {
                $conn->rollback();
                $errorMessage = "Error inserting into sit_in: " . $insertStmt->error;
            }
        }

        $conn->commit();
         $successMessage = "Reservation status updated successfully!";
    } else {
        $errorMessage = "Error updating reservation: " . $updateStmt->error;
    }
}

// Get filter values from GET parameters
$lab_filter = isset($_GET['lab']) ? $_GET['lab'] : '';
$date_filter = isset($_GET['date']) ? $_GET['date'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Prepare query with possible filters
$query = "SELECT r.*, u.firstname, u.lastname 
          FROM reservations r 
          JOIN users u ON r.idno = u.idno 
          WHERE 1=1";
$params = [];
$types = "";

if (!empty($lab_filter)) {
    $query .= " AND r.lab_name = ?";
    $params[] = $lab_filter;
    $types .= "s";
}

if (!empty($date_filter)) {
    $query .= " AND r.reservation_date = ?";
    $params[] = $date_filter;
    $types .= "s";
}

if (!empty($status_filter)) {
    $query .= " AND r.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

$query .= " ORDER BY r.reservation_date DESC, r.created_at DESC";

// Prepare and execute the query
$stmt = $conn->prepare($query);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Fetch all labs for dropdown filter
$labsQuery = "SELECT DISTINCT lab_name FROM reservations ORDER BY lab_name";
$labsResult = $conn->query($labsQuery);
?>

<div class="flex min-h-screen bg-gray-50 text-gray-900 pb-14">
    <?php include '../shared/aside.php'; ?>
    <main class="flex-1 p-4 ml-64">
        <div class="max-w-[1400px] mx-auto">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-semibold text-gray-800">Reservation Management</h1>
                    <p class="text-lg text-gray-600">Approve, reject, or mark reservations as completed</p>
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

            <!-- Filters -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="lab" class="block text-sm font-medium text-gray-700 mb-2">Lab</label>
                        <select id="lab" name="lab" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Labs</option>
                            <?php while ($lab = $labsResult->fetch_assoc()): ?>
                                <option value="<?php echo $lab['lab_name']; ?>" <?php if ($lab_filter === $lab['lab_name']) echo 'selected'; ?>>
                                    <?php echo $lab['lab_name']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                        <input type="date" id="date" name="date" value="<?php echo $date_filter; ?>" 
                               class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select id="status" name="status" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Statuses</option>
                            <option value="pending" <?php if ($status_filter === 'pending') echo 'selected'; ?>>Pending</option>
                            <option value="approved" <?php if ($status_filter === 'approved') echo 'selected'; ?>>Approved</option>
                            <option value="rejected" <?php if ($status_filter === 'rejected') echo 'selected'; ?>>Rejected</option>
                            <option value="completed" <?php if ($status_filter === 'completed') echo 'selected'; ?>>Completed</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors mr-2">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                        <a href="reservation_management.php" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                            <i class="fas fa-eraser mr-2"></i>Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Reservations Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lab & PC</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($reservation = $result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-medium text-gray-900"><?php echo htmlspecialchars($reservation['firstname'] . ' ' . $reservation['lastname']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($reservation['idno']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-medium text-gray-900"><?php echo htmlspecialchars($reservation['lab_name']); ?></div>
                                            <div class="text-sm text-gray-500">PC #<?php echo htmlspecialchars($reservation['pc_number']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="font-medium text-gray-900"><?php echo date('F j, Y', strtotime($reservation['reservation_date'])); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($reservation['time_slot']); ?></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if ($reservation['status'] === 'pending'): ?>
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                    Pending
                                                </span>
                                            <?php elseif ($reservation['status'] === 'approved'): ?>
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                    Approved
                                                </span>
                                            <?php elseif ($reservation['status'] === 'rejected'): ?>
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                    Rejected
                                                </span>
                                            <?php elseif ($reservation['status'] === 'completed'): ?>
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                    Completed
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('M j, Y g:i A', strtotime($reservation['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <div class="flex space-x-2">
                                                <?php if ($reservation['status'] === 'pending'): ?>
                                                    <form method="POST" class="inline">
                                                        <input type="hidden" name="reservation_id" value="<?php echo $reservation['reservation_id']; ?>">
                                                        <input type="hidden" name="new_status" value="approved">
                                                        <button type="submit" name="update_status" class="text-green-600 hover:text-green-900" title="Approve">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" class="inline">
                                                        <input type="hidden" name="reservation_id" value="<?php echo $reservation['reservation_id']; ?>">
                                                        <input type="hidden" name="new_status" value="rejected">
                                                        <button type="submit" name="update_status" class="text-red-600 hover:text-red-900" title="Reject">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                <?php elseif ($reservation['status'] === 'approved'): ?>
                                                    <form method="POST" class="inline">
                                                        <input type="hidden" name="reservation_id" value="<?php echo $reservation['reservation_id']; ?>">
                                                        <input type="hidden" name="new_status" value="completed">
                                                        <button type="submit" name="update_status" class="text-blue-600 hover:text-blue-900" title="Mark as Completed">
                                                            <i class="fas fa-check-double"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                        No reservations found matching your criteria.
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

<script>
    // Confirm before changing status
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const status = this.querySelector('input[name="new_status"]').value;
            const statusText = status.charAt(0).toUpperCase() + status.slice(1);
            
            if (!confirm(`Are you sure you want to change the status to "${statusText}"?`)) {
                e.preventDefault();
            }
        });
    });
</script>

<?php require_once '../shared/footer.php'; ?> 