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
    $whereClause = "WHERE status = ?";
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
$reservationsQuery = "SELECT * FROM reservations " . $whereClause . " ORDER BY created_at DESC LIMIT ? OFFSET ?";
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

// Get the counts for each status
$statusCounts = [];
$statusTypes = ['pending', 'approved', 'rejected', 'completed'];

foreach ($statusTypes as $status) {
    $countStatusQuery = "SELECT COUNT(*) as count FROM reservations WHERE status = ?";
    $countStatusStmt = $conn->prepare($countStatusQuery);
    $countStatusStmt->bind_param("s", $status);
    $countStatusStmt->execute();
    $countStatusResult = $countStatusStmt->get_result();
    $statusCounts[$status] = $countStatusResult->fetch_assoc()['count'];
}
?>

<div class="flex min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 text-gray-900 pb-14">
    <main class="flex-1 p-6 pt-24">
        <div class="max-w-7xl mx-auto">
            <!-- Welcome Section -->
            <div class="bg-white bg-opacity-80 backdrop-blur-sm rounded-xl shadow-md p-6 mb-8 border border-gray-100">
                <div class="flex items-center space-x-4">
                    <div class="bg-indigo-100 p-3 rounded-full">
                        <i class="fas fa-calendar-check text-2xl text-indigo-600"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Manage Reservations</h1>
                        <p class="text-lg text-gray-600">Review and manage lab reservations</p>
                    </div>
                </div>
            </div>

            <!-- Totals Section -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 text-white p-6 rounded-xl shadow-md hover:shadow-xl transform hover:scale-105 transition duration-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-yellow-100">Pending</h2>
                        <div class="bg-white/20 p-2 rounded-lg">
                            <i class="fas fa-clock text-xl"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold mt-2"><?php echo number_format($statusCounts['pending']); ?></p>
                    <p class="text-yellow-200 text-sm mt-1">Reservations awaiting review</p>
                </div>
                
                <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-6 rounded-xl shadow-md hover:shadow-xl transform hover:scale-105 transition duration-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-green-100">Approved</h2>
                        <div class="bg-white/20 p-2 rounded-lg">
                            <i class="fas fa-check text-xl"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold mt-2"><?php echo number_format($statusCounts['approved']); ?></p>
                    <p class="text-green-200 text-sm mt-1">Approved reservations</p>
                </div>
                
                <div class="bg-gradient-to-br from-red-500 to-red-600 text-white p-6 rounded-xl shadow-md hover:shadow-xl transform hover:scale-105 transition duration-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-red-100">Rejected</h2>
                        <div class="bg-white/20 p-2 rounded-lg">
                            <i class="fas fa-times text-xl"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold mt-2"><?php echo number_format($statusCounts['rejected']); ?></p>
                    <p class="text-red-200 text-sm mt-1">Rejected reservations</p>
                </div>
                
                <div class="bg-gradient-to-br from-gray-500 to-gray-600 text-white p-6 rounded-xl shadow-md hover:shadow-xl transform hover:scale-105 transition duration-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-gray-100">Completed</h2>
                        <div class="bg-white/20 p-2 rounded-lg">
                            <i class="fas fa-flag-checkered text-xl"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold mt-2"><?php echo number_format($statusCounts['completed']); ?></p>
                    <p class="text-gray-200 text-sm mt-1">Completed reservations</p>
                </div>
            </div>

            <!-- Filter -->
            <div class="bg-white p-6 rounded-xl shadow-md mb-8 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-filter text-indigo-600 mr-2"></i>
                        Filter Reservations
                    </h2>
                </div>
                <form method="GET" class="flex flex-wrap items-center gap-4">
                    <div class="flex-grow max-w-sm">
                        <select name="status" class="w-full border border-gray-300 rounded-md px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                            <option value="">All Statuses</option>
                            <option value="pending" <?php echo ($statusFilter == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo ($statusFilter == 'approved') ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo ($statusFilter == 'rejected') ? 'selected' : ''; ?>>Rejected</option>
                            <option value="completed" <?php echo ($statusFilter == 'completed') ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-colors shadow-sm flex items-center justify-center">
                        <i class="fas fa-search mr-2"></i>
                        Apply Filter
                    </button>
                    <?php if (!empty($statusFilter)): ?>
                        <a href="manage_reservations.php" class="px-5 py-2 rounded-lg bg-gray-500 text-white hover:bg-gray-600 transition-colors shadow-sm flex items-center justify-center">
                            <i class="fas fa-times mr-2"></i>
                            Clear Filter
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Reservations Table -->
            <div class="bg-white p-6 rounded-xl shadow-md mb-8 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-list text-indigo-600 mr-2"></i>
                        Reservation List
                    </h2>
                    <div class="text-sm text-gray-500">
                        Showing <?php echo min($limit, $reservationsResult->num_rows); ?> of <?php echo $totalRecords; ?> reservations
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reservation ID</th>
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
                                    <tr class="hover:bg-gray-50 transition-colors" data-id="<?php echo $reservation['reservation_id']; ?>">
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $reservation['reservation_id']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $reservation['idno']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $reservation['full_name']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $reservation['lab_name']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $reservation['pc_number']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M d, Y', strtotime($reservation['reservation_date'])); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $reservation['time_slot']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo $reservation['purpose']; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            <?php 
                                            echo match($reservation['status']) {
                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                'approved' => 'bg-green-100 text-green-800',
                                                'rejected' => 'bg-red-100 text-red-800',
                                                'completed' => 'bg-gray-100 text-gray-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                            ?>">
                                                <?php echo ucfirst($reservation['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <?php if ($reservation['status'] == 'pending'): ?>
                                                <button class="accept-btn text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 font-medium py-1.5 px-4 rounded-lg transition-all shadow-sm" data-id="<?php echo $reservation['reservation_id']; ?>">
                                                    <i class="fas fa-check mr-1"></i> Accept
                                                </button>
                                                <button class="reject-btn text-white bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 font-medium py-1.5 px-4 rounded-lg transition-all shadow-sm" data-id="<?php echo $reservation['reservation_id']; ?>">
                                                    <i class="fas fa-times mr-1"></i> Reject
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="px-6 py-8 whitespace-nowrap text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <i class="fas fa-calendar-times text-4xl text-gray-300 mb-3"></i>
                                            <p class="text-lg">No reservations found</p>
                                            <?php if (!empty($statusFilter)): ?>
                                                <p class="text-sm mt-1">Try clearing your filter or checking back later</p>
                                            <?php endif; ?>
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
                <div class="flex justify-center">
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?><?php echo !empty($statusFilter) ? '&status=' . $statusFilter : ''; ?>" class="relative inline-flex items-center px-3 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 transition-colors">
                                <span class="sr-only">Previous</span>
                                <i class="fas fa-chevron-left text-xs"></i>
                            </a>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <a href="?page=<?php echo $i; ?><?php echo !empty($statusFilter) ? '&status=' . $statusFilter : ''; ?>" class="<?php echo ($i == $page) ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'; ?> relative inline-flex items-center px-4 py-2 border text-sm font-medium transition-colors">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?><?php echo !empty($statusFilter) ? '&status=' . $statusFilter : ''; ?>" class="relative inline-flex items-center px-3 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 transition-colors">
                                <span class="sr-only">Next</span>
                                <i class="fas fa-chevron-right text-xs"></i>
                            </a>
                        <?php endif; ?>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

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
            // Disable buttons to prevent multiple clicks
            const buttons = document.querySelectorAll(`.accept-btn[data-id="${reservationId}"], .reject-btn[data-id="${reservationId}"]`);
            buttons.forEach(btn => {
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
            });

            fetch('update_reservation_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `reservation_id=${reservationId}&status=${status}`,
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Create a nice toast notification
                    const notificationDiv = document.createElement('div');
                    notificationDiv.className = 'fixed top-4 right-4 bg-white border-l-4 border-green-500 text-green-700 p-4 rounded shadow-lg z-50 transform transition-all duration-500 translate-x-full';
                    notificationDiv.innerHTML = `
                        <div class="flex items-center">
                            <div class="mr-3">
                                <i class="fas fa-check-circle text-green-500 text-xl"></i>
                            </div>
                            <div>
                                <p class="font-bold">Success!</p>
                                <p class="text-sm">${status === 'approved' ? 'Reservation approved successfully.' : 'Reservation rejected successfully.'}</p>
                            </div>
                            <button class="ml-4 text-gray-400 hover:text-gray-600" onclick="this.parentNode.parentNode.remove()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                    
                    document.body.appendChild(notificationDiv);
                    
                    // Slide in animation
                    setTimeout(() => {
                        notificationDiv.classList.remove('translate-x-full');
                    }, 10);
                    
                    // Remove notification after 3 seconds
                    setTimeout(() => {
                        notificationDiv.classList.add('translate-x-full');
                        setTimeout(() => {
                            notificationDiv.remove();
                        }, 500);
                    }, 3000);

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
                    
                    // Update the status counts at the top
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    // Show error notification
                    const notificationDiv = document.createElement('div');
                    notificationDiv.className = 'fixed top-4 right-4 bg-white border-l-4 border-red-500 text-red-700 p-4 rounded shadow-lg z-50 transform transition-all duration-500 translate-x-full';
                    notificationDiv.innerHTML = `
                        <div class="flex items-center">
                            <div class="mr-3">
                                <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                            </div>
                            <div>
                                <p class="font-bold">Error!</p>
                                <p class="text-sm">${data.message || 'Error updating reservation status.'}</p>
                            </div>
                            <button class="ml-4 text-gray-400 hover:text-gray-600" onclick="this.parentNode.parentNode.remove()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                    
                    document.body.appendChild(notificationDiv);
                    
                    // Slide in animation
                    setTimeout(() => {
                        notificationDiv.classList.remove('translate-x-full');
                    }, 10);
                    
                    // Remove notification after 3 seconds
                    setTimeout(() => {
                        notificationDiv.classList.add('translate-x-full');
                        setTimeout(() => {
                            notificationDiv.remove();
                        }, 500);
                    }, 3000);

                    // Re-enable the buttons
                    buttons.forEach(btn => {
                        btn.disabled = false;
                        btn.classList.remove('opacity-50', 'cursor-not-allowed');
                    });

                    console.error('Error updating reservation status:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Show error notification
                const notificationDiv = document.createElement('div');
                notificationDiv.className = 'fixed top-4 right-4 bg-white border-l-4 border-red-500 text-red-700 p-4 rounded shadow-lg z-50 transform transition-all duration-500 translate-x-full';
                notificationDiv.innerHTML = `
                    <div class="flex items-center">
                        <div class="mr-3">
                            <i class="fas fa-exclamation-circle text-red-500 text-xl"></i>
                        </div>
                        <div>
                            <p class="font-bold">Error!</p>
                            <p class="text-sm">Network error. Please try again.</p>
                        </div>
                        <button class="ml-4 text-gray-400 hover:text-gray-600" onclick="this.parentNode.parentNode.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                
                document.body.appendChild(notificationDiv);
                
                // Slide in animation
                setTimeout(() => {
                    notificationDiv.classList.remove('translate-x-full');
                }, 10);
                
                // Remove notification after 3 seconds
                setTimeout(() => {
                    notificationDiv.classList.add('translate-x-full');
                    setTimeout(() => {
                        notificationDiv.remove();
                    }, 500);
                }, 3000);

                // Re-enable the buttons
                buttons.forEach(btn => {
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                });
            });
        }
    });
</script>
<?php require_once '../shared/footer.php'; ?>