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
    $params[] = $limit; // Assuming the limit is a number (integer)
    $params[] = $offset;
    $types .= 'ii';
    $reservationsStmt->bind_param($types, ...$params);
} else {
    $reservationsStmt->bind_param('ii', $limit, $offset);
}

$reservationsStmt->execute();
$reservationsResult = $reservationsStmt->get_result();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
 header("Location: ../login.php");
 exit;
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<div class="flex min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 text-gray-900 pb-14">
    <?php include '../shared/aside.php'; ?>
    <main class="flex-1 p-6 pt-24">
        <div class="max-w-7xl mx-auto">
            <!-- Welcome Section -->
            <div class="bg-white bg-opacity-80 backdrop-blur-sm rounded-xl shadow-md p-6 mb-8 border border-gray-100">
                <div class="flex items-center space-x-4">
                    <div class="bg-indigo-100 p-3 rounded-full">
                        <i class="fas fa-calendar-check text-2xl text-indigo-600"></i>
                    </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Manage Reservations</h1>
                    <p class="text-lg text-gray-600">Review and manage lab reservations</p>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6 border border-gray-100">
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
            <div class="bg-white rounded-xl shadow-md mb-6 border border-gray-100 overflow-hidden">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">All Reservations</h2>
                        <p class="text-sm text-gray-500 mt-1">Review and manage incoming and past lab reservations</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Full Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lab Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PC Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reservation Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time Slot</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purpose</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="reservationsTableBody" class="divide-y divide-gray-200"></tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pagination -->
            <div id="pagination" class="flex justify-between items-center mb-6 bg-white p-4 rounded-xl shadow-md border border-gray-100"></div>
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
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        fetchData(); // Reload data via AJAX on success
                    } else {
                        alert('Error updating reservation status: ' + response.message);
                    }
                } catch (e) {
                    alert('Error parsing response from server: ' + xhr.responseText);
                } else {
                    alert('Error updating reservation status: ' + xhr.responseText);
                }
            }
        };
        
        // Send the POST request
        xhr.send('reservation_id=' + reservationId + '&status=' + status);
    }

    // --- New JavaScript for Data Fetching and Pagination (similar to sit-in) ---

    document.addEventListener("DOMContentLoaded", function () {
        // Function to fetch reservation records data
        function fetchData(page = 1) {
            let statusFilter = document.querySelector('select[name="status"]').value;

            fetch(`fetch_reservations.php?page=${page}&status=${encodeURIComponent(statusFilter)}`)
                .then(response => response.json())
                .then(data => {
                    displayReservations(data.reservations);
                    createPagination(data.pagination);
                })
                .catch(error => {
                    console.error("Error fetching data:", error);
                    // Display an error message in the table body
                    document.getElementById("reservationsTableBody").innerHTML = `
                        <tr>
                            <td colspan="9" class="text-center py-4 text-red-500">
                                <i class="fas fa-exclamation-triangle mr-2"></i> Error loading reservations.
                            </td>
                        </tr>
                    `;
                });
        }

        // Function to display reservations in the table
        function displayReservations(reservations) {
            const tbody = document.getElementById("reservationsTableBody");
            tbody.innerHTML = ''; // Clear existing rows

            if (reservations.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-center py-4 text-gray-500">No reservations found</td>
                    </tr>
                `;
                return;
            }

            reservations.forEach(reservation => {
                const row = document.createElement('tr');
                row.classList.add("hover:bg-gray-100");
                row.setAttribute('data-id', reservation.reservation_id);

                const statusClass = (reservation.status === 'pending') ? 'bg-yellow-100 text-yellow-800' :
                                    ((reservation.status === 'approved') ? 'bg-green-100 text-green-800' :
                                    ((reservation.status === 'rejected') ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'));

                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">${reservation.idno}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${reservation.full_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${reservation.lab_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${reservation.pc_number}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${new Date(reservation.reservation_date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${reservation.time_slot}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${reservation.purpose}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                            ${reservation.status.charAt(0).toUpperCase() + reservation.status.slice(1)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-left">
                        ${reservation.status === 'pending' ? `
                        <button class="accept-btn bg-green-500 text-white px-4 py-2 rounded-md" data-id="${reservation.reservation_id}">Accept</button>
                        <button class="reject-btn bg-red-500 text-white px-4 py-2 rounded-md" data-id="${reservation.reservation_id}">Reject</button>
                        ` : ''}
                    </td>
                `;
                tbody.appendChild(row);
            });
            
            // Re-attach event listeners to the new buttons
            attachButtonListeners();
        }

        // Function to create pagination controls (copy from sit-in records and adapt)
        function createPagination(pagination) {
            // ... (Implementation will be similar to the createPagination function in the provided sit-in records code) ...
            // Need to adapt event listener to call fetchData(page)
        }

        // Initial fetch when the page loads
        fetchData();
    });
</script>
