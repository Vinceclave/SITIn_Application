<?php
require_once '../config/config.php';
session_start();

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

// Ensure role is set
$role = isset($user['role']) ? $user['role'] : 'Student';

// Check if user is admin
if (strcasecmp($role, 'Admin') !== 0) {
    header("Location: home.php");
    exit;
}

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// Filter
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$whereClause = '';
$params = [];
$types = '';

if (!empty($statusFilter)) {
    $whereClause = "WHERE r.status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

// Count total records
$countQuery = "SELECT COUNT(*) as total FROM reservations " . $whereClause;
$countStmt = $conn->prepare($countQuery);

if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}

$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRecords = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $limit);

// Fetch reservations
$reservationsQuery = "
    SELECT 
        r.*, 
        u.idno,
        CONCAT(u.firstname, ' ', u.lastname) AS full_name
    FROM 
        reservations r
    JOIN 
        users u ON r.idno = u.idno
    " . $whereClause . " 
    ORDER BY r.created_at DESC LIMIT ? OFFSET ?";
$reservationsStmt = $conn->prepare($reservationsQuery);

if (!empty($params)) {
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';
    $reservationsStmt->bind_param($types, ...$params);
} else {
    $reservationsStmt->bind_param('ii', $limit, $offset);
}

$reservationsStmt->execute();
$reservationsResult = $reservationsStmt->get_result();
?>

<div class="mt-10 flex min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 text-gray-900 pb-14">
    <?php include '../shared/aside.php'; ?>
    <main class="flex-1 pt-10 p-6">
        <div class="max-w-[1400px] mx-auto">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Manage Reservations</h1>
                    <p class="text-lg text-gray-600">Review and manage lab reservations</p>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="mb-6">
                <form method="GET" class="flex items-center space-x-4">
                    <select name="status" class="border border-gray-300 rounded-md px-4 py-2">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo ($statusFilter == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo ($statusFilter == 'approved') ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo ($statusFilter == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                        <option value="completed" <?php echo ($statusFilter == 'completed') ? 'selected' : ''; ?>>Completed</option>
                    </select>
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">Filter</button>
                    <?php if (!empty($statusFilter)): ?>
                        <a href="manage_reservations.php" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 transition-colors">Clear Filter</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Reservations Table Section -->
            <div class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100 mb-8">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">All Reservations</h2>
                        <p class="text-sm text-gray-500 mt-1">Review and manage incoming and past lab reservations</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lab Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PC Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reservation Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time Slot</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purpose</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if ($reservationsResult->num_rows > 0): ?>
                                <?php while ($reservation = $reservationsResult->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-100" data-id="<?php echo $reservation['reservation_id']; ?>">
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $reservation['idno']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $reservation['full_name']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $reservation['lab_name']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $reservation['pc_number']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M d, Y', strtotime($reservation['reservation_date'])); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $reservation['time_slot']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $reservation['purpose']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                <?php echo ($reservation['status'] == 'pending') ? 'bg-yellow-100 text-yellow-800' : 
                                                    (($reservation['status'] == 'approved') ? 'bg-green-100 text-green-800' : 
                                                    (($reservation['status'] == 'rejected') ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800')) ?>">
                                                <?php echo ucfirst($reservation['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <button class="accept-btn bg-green-500 text-white px-4 py-2 rounded-md" data-id="<?php echo $reservation['reservation_id']; ?>">Accept</button>
                                            <button class="reject-btn bg-red-500 text-white px-4 py-2 rounded-md" data-id="<?php echo $reservation['reservation_id']; ?>">Reject</button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="9" class="text-center py-4 text-gray-500">No reservations found</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4 flex justify-between">
                    <nav>
                        <ul class="inline-flex space-x-2">
                            <li>
                                <a href="?page=1" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-l-lg">First</a>
                            </li>
                            <li>
                                <a href="?page=<?php echo max(1, $page - 1); ?>" class="px-4 py-2 text-gray-700 border border-gray-300">Prev</a>
                            </li>
                            <li>
                                <a href="?page=<?php echo min($totalPages, $page + 1); ?>" class="px-4 py-2 text-gray-700 border border-gray-300">Next</a>
                            </li>
                            <li>
                                <a href="?page=<?php echo $totalPages; ?>" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-r-lg">Last</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </main>
</div>

<?php
// Close database connection
$conn->close();
?>

<script>
    // Handle Accept and Reject actions via AJAX
    document.querySelectorAll('.accept-btn').forEach(button => {
        button.addEventListener('click', function() {
            let reservationId = this.getAttribute('data-id');
            updateReservationStatus(reservationId, 'approved');
        });
    });

    document.querySelectorAll('.reject-btn').forEach(button => {
        button.addEventListener('click', function() {
            let reservationId = this.getAttribute('data-id');
            updateReservationStatus(reservationId, 'rejected');
        });
    });

    function updateReservationStatus(reservationId, status) {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'update_reservation_status.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                if (xhr.responseText === 'success') {
                    // Reload the page to reflect the status change
                    location.reload();
                } else {
                    alert('Error updating reservation status: ' + xhr.responseText);
                }
            }
        };
        
        // Send the POST request
        xhr.send('reservation_id=' + reservationId + '&status=' + status);
    }
</script>
