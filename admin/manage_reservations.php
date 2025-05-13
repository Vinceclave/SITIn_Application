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
require_once '../shared/aside.php';


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
        u.id_number as idno,
        u.full_name as full_name
    FROM 
        reservations r
    JOIN 
        users u ON r.user_id = u.id
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

        <!-- Filter -->
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

        <!-- Reservations Table -->
        <div class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100 mb-8">
             <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">All Reservations</h2>
                        <p class="text-sm text-gray-500 mt-1">Review and manage incoming and past lab reservations</p>
                    </div>
                    <button class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
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
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo ($reservation['status'] == 'pending') ? 'bg-yellow-100 text-yellow-800' : (($reservation['status'] == 'approved') ? 'bg-green-100 text-green-800' : (($reservation['status'] == 'rejected') ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')); ?>">
                                             <?php echo ucfirst(htmlspecialchars($reservation['status'])); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <?php if ($reservation['status'] == 'pending'): ?>
                                            <button class="accept-btn bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded" data-id="<?php echo $reservation['reservation_id']; ?>">Accept</button>
                                            <button class="reject-btn bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" data-id="<?php echo $reservation['reservation_id']; ?>">Reject</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">No reservations found.</td>
                            </tr> 
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="mt-6 flex justify-center">
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo !empty($statusFilter) ? '&status=' . $statusFilter : ''; ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Previous</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo !empty($statusFilter) ? '&status=' . $statusFilter : ''; ?>" class="<?php echo ($i == $page) ? 'bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'; ?> relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo !empty($statusFilter) ? '&status=' . $statusFilter : ''; ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            <span class="sr-only">Next</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        <?php endif; ?>
    </div>
    </main>
</div>

<?php require_once '../shared/footer.php'; ?>

<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const acceptButtons = document.querySelectorAll('.accept-btn');
        const rejectButtons = document.querySelectorAll('.reject-btn');

        acceptButtons.forEach(button => {
            button.addEventListener('click', function() {
                const reservationId = this.dataset.id;
                updateReservationStatus(reservationId, 'approved');
            });
        });

        rejectButtons.forEach(button => {
            button.addEventListener('click', function() {
                const reservationId = this.dataset.id;
                updateReservationStatus(reservationId, 'rejected');
            });
        });

        function updateReservationStatus(reservationId, status) {
            fetch('update_reservation_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `reservation_id=${reservationId}&status=${status}`,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const row = document.querySelector(`tr[data-id="${reservationId}"]`);
                    const statusCell = row.querySelector('td:nth-child(9) span');
                    const actionsCell = row.querySelector('td:last-child');

                    statusCell.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                    statusCell.classList.remove('bg-yellow-100', 'text-yellow-800', 'bg-green-100', 'text-green-800', 'bg-red-100', 'text-red-800', 'bg-gray-100','text-gray-800');
                    if (status === 'approved') {
                        statusCell.classList.add('bg-green-100', 'text-green-800');
                    } else if (status === 'rejected') {
                        statusCell.classList.add('bg-red-100', 'text-red-800');
                    } else if (status === 'completed') {
                        statusCell.classList.add('bg-gray-100', 'text-gray-800');
                    } else {
                        statusCell.classList.add('bg-yellow-100', 'text-yellow-800');
                    }
                    
                    actionsCell.innerHTML = ''; 
                } else {
                    console.error('Error updating reservation status:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    });
</script>