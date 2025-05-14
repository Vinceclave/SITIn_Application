php
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
$countQuery = "SELECT COUNT(*) as total FROM reservations r " . $whereClause;
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

// Fetch labs for displaying PC availability
$labsQuery = "SELECT * FROM labs";
$labsResult = $conn->query($labsQuery);
$labs = [];
while ($lab = $labsResult->fetch_assoc()) {
    $labs[] = $lab;
}

// Fetch active sit-in sessions by joining sit_in and pcs tables
$activeSitinQuery = "
    SELECT
        pc.lab_name,
        pc.pc_number,
        si.idno AS student_idno
    FROM
        sit_in si
    JOIN
        pcs pc ON si.lab = pc.lab_name
    WHERE
        si.out_time IS NULL"; // Assuming out_time is NULL for active sessions
$activeSitinResult = $conn->query($activeSitinQuery);
$activeSitinPCs = [];
if ($activeSitinResult) { // Check if query was successful
    while ($row = $activeSitinResult->fetch_assoc()) {
        $activeSitinPCs[$row['lab_name'] . '-' . $row['pc_number']] = $row['student_idno'];
    }
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
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Manage Reservations</h1>
                    <p class="text-lg text-gray-600">Review and manage lab reservations</p>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6 border border-gray-100" id="filterSection">
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
                        <tbody id="reservationsTableBody" class="divide-y divide-gray-200"><!-- Reservation data will be loaded here by JavaScript --></tbody>
                    </table>
                </div>
                <div id="pagination" class="p-6 flex justify-between items-center">

                </div>
            </div>

            <!-- PC Availability Section -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">PC Availability & Sit-in</h2>
                <?php if (empty($labs)): ?>
                    <p class="text-gray-500">No labs configured.</p>
                <?php else: ?>
                    <?php foreach ($labs as $lab): ?>
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-700 mb-3"><?php echo htmlspecialchars($lab['lab_name']); ?></h3>
                            <div class="grid grid-cols-auto-fill-sm gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <?php for ($i = 1; $i <= $lab['total_pcs']; $i++): ?>
                                    <?php
                                    $pcIdentifier = $lab['lab_name'] . '-' . $i;
                                    $isOccupied = isset($activeSitinPCs[$pcIdentifier]);
                                    $statusClass = $isOccupied ? 'bg-red-100 text-red-800 border-red-200 cursor-not-allowed' : 'bg-green-100 text-green-800 border-green-200 cursor-pointer hover:bg-green-200';
                                    $statusText = $isOccupied ? 'Occupied' : 'Available';
                                    $studentIdno = $isOccupied ? $activeSitinPCs[$pcIdentifier] : '';
                                    ?>
                                    <div class="flex flex-col items-center justify-center p-4 rounded-md border shadow-sm text-center <?php echo $statusClass; ?>"
                                         data-lab-name="<?php echo htmlspecialchars($lab['lab_name']); ?>"
                                         data-pc-number="<?php echo htmlspecialchars($i); ?>"
                                         <?php echo !$isOccupied ? 'onclick="openSitInModal(\'' . htmlspecialchars($lab['lab_name']) . '\', \'' . htmlspecialchars($i) . '\')"' : ''; ?>>
                                        <i class="fas fa-desktop text-2xl mb-2"></i>
                                        <span class="font-semibold">PC <?php echo $i; ?></span>
                                        <span class="text-xs"><?php echo $statusText; ?></span>
                                        <?php if ($isOccupied): ?>
                                            <span class="text-xs mt-1">Student ID: <?php echo htmlspecialchars($studentIdno); ?></span>
                                            <button class="mt-2 px-3 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600" onclick="event.stopPropagation(); endSitIn('<?php echo htmlspecialchars($pcIdentifier); ?>')">End Sit-in</button>
                                        <?php endif; ?>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php foreach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sit-in Modal -->
        <div id="sitInModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Initiate Sit-in</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500" id="sitInModalDetails"></p>
                        <form id="sitInForm" class="mt-4">
                             <input type="hidden" id="modalLabName" name="lab_name">
                             <input type="hidden" id="modalPcNumber" name="pc_number">
                            <div class="mb-4">
                                <label for="studentIdno" class="block text-left text-sm font-medium text-gray-700">Student ID Number</label>
                                <input type="text" id="studentIdno" name="student_idno" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                            <div class="mb-4">
                                <label for="reason" class="block text-left text-sm font-medium text-gray-700">Reason for Sit-in</label>
                                <textarea id="reason" name="reason" rows="3" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                            </div>
                            <div class="items-center px-4 py-3">
                                <button type="submit" class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-300 mr-2">
                                    Start Sit-in
                                </button>
                                <button type="button" onclick="closeSitInModal()" class="px-4 py-2 bg-gray-400 text-white text-base font-medium rounded-md shadow-sm hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-300">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let currentPage = 1;

        // Function to open the sit-in modal
        window.openSitInModal = function(labName, pcNumber) {
            document.getElementById('modalLabName').value = labName;
            document.getElementById('modalPcNumber').value = pcNumber;
            document.getElementById('sitInModalDetails').textContent = `Initiate sit-in for Lab: ${labName}, PC Number: ${pcNumber}`;
            document.getElementById('sitInModal').classList.remove('hidden');
        }

        // Function to close the sit-in modal
        window.closeSitInModal = function() {
            document.getElementById('sitInModal').classList.add('hidden');
            document.getElementById('sitInForm').reset(); // Reset form fields
        }

        // Handle Sit-in Form Submission
        document.getElementById('sitInForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            initiateSitIn(formData);
        });

        // Function to initiate sit-in
        function initiateSitIn(formData) {
            fetch('sitting_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Sit-in initiated successfully!');
                    closeSitInModal();
                    // Refresh the PC availability section
                    location.reload(); // Simple reload for now, can optimize later
                } else {
                    alert('Error initiating sit-in: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while initiating sit-in.');
            });
        }

        // Function to end sit-in
        window.endSitIn = function(pcIdentifier) { // Changed parameter name to reflect the identifier
            if (confirm('Are you sure you want to end the sit-in for this PC?')) {
                // Split the identifier to get labName and pcNumber
                const [labName, pcNumber] = pcIdentifier.split('-');
                fetch('sit_in_end.php', { // Assuming sit_in_end.php handles ending sit-in
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    // Pass labName and pcNumber to the end sit-in script
                    body: 'lab_name=' + encodeURIComponent(labName) + '&pc_number=' + encodeURIComponent(pcNumber)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Sit-in ended successfully!');
                        // Refresh the PC availability section
                        location.reload(); // Simple reload for now
                    } else {
                        alert('Error ending sit-in: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while ending sit-in.');
                });
            }
        }

        // Function to fetch reservation data
        function fetchReservations(page = 1) {
            let statusFilter = document.querySelector('select[name="status"]').value;

            fetch(`fetch_reservations.php?page=${page}&status=${encodeURIComponent(statusFilter)}`) // Updated URL
                .then(response => response.json())
                .then(data => {
                    displayReservations(data.reservations);
                    createPagination(data.pagination);
                    attachActionListeners(); // Re-attach listeners after data load
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

        // Function to display reservations

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

                const reservationDate = new Date(reservation.reservation_date);
                const formattedDate = reservationDate.toLocaleDateString('en-US', {
                    month: 'short', day: 'numeric', year: 'numeric'
                });

                const statusClass = (reservation.status === 'pending') ? 'bg-yellow-100 text-yellow-800' :
                                    ((reservation.status === 'approved') ? 'bg-green-100 text-green-800' :
                                    ((reservation.status === 'rejected') ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'));

                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">${reservation.idno}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${reservation.full_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${reservation.lab_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${reservation.pc_number}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${reservation.time_slot}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${reservation.purpose}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                            ${reservation.status.charAt(0).toUpperCase() + reservation.status.slice(1)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-left">
                        ${reservation.status === 'pending' ? `
                        <button class="accept-btn bg-green-500 text-white px-4 py-2 rounded-md text-xs mr-2" data-id="${reservation.reservation_id}">Accept</button>
                        <button class="reject-btn bg-red-500 text-white px-4 py-2 rounded-md text-xs" data-id="${reservation.reservation_id}">Reject</button>
                        ` : ''}
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Function to attach event listeners

        function attachActionListeners() {
            document.querySelectorAll('.accept-btn').forEach(button => {
                // Remove existing listeners before adding new ones
                button.removeEventListener('click', handleActionListener); // Prevent duplicate listeners
                button.addEventListener('click', handleActionListener);
            });
            document.querySelectorAll('.reject-btn').forEach(button => {
                button.removeEventListener('click', handleActionListener); // Prevent duplicate listeners
                button.addEventListener('click', handleActionListener);
            });
        }

        // Handler for action button clicks

        function handleActionListener() {
            const reservationId = this.getAttribute('data-id');
            const status = this.classList.contains('accept-btn') ? 'approved' : 'rejected';
            updateReservationStatus(reservationId, status);
        }
        // Function to update reservation status

        function updateReservationStatus(reservationId, status) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'update_reservation_status.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            // Refresh data after successful update
                            fetchReservations(currentPage); // Corrected function call
                        } else {
                            alert('Error updating reservation status: ' + response.message);
                        }
                    } catch (e) {
                        alert('Error parsing response from server: ' + xhr.responseText);
                    }
                } else {
                    alert('Error updating reservation status: Server returned status ' + xhr.status);
                }
            };

            xhr.onerror = function() {
                alert('Network error occurred while updating reservation status.');
            };

            // Send the POST request
            xhr.send('reservation_id=' + reservationId + '&status=' + status);
        }

        // Function to create pagination

        function createPagination(pagination) {
            const paginationElement = document.getElementById('pagination');
            paginationElement.innerHTML = ''; // Clear existing pagination

            if (pagination.totalPages <= 1) {
                return; // No pagination needed
            }

            currentPage = pagination.currentPage; // Update current page tracker

            // Create info text
            const infoDiv = document.createElement('div');
            infoDiv.className = 'text-sm text-gray-500';
            infoDiv.textContent = `Page ${pagination.currentPage} of ${pagination.totalPages} (${pagination.totalRecords} records)`;
            paginationElement.appendChild(infoDiv);

            // Create a container for the pagination buttons
            const buttonsDiv = document.createElement('div');
            buttonsDiv.className = 'flex space-x-2';

            // Previous button
            if (pagination.currentPage > 1) {
                buttonsDiv.appendChild(createPaginationButton('Prev', pagination.currentPage - 1));
            }

            // Page number buttons (simplified for brevity, can add ellipsis logic)
            const maxVisiblePages = 5;
            let startPage = Math.max(1, pagination.currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(pagination.totalPages, startPage + maxVisiblePages - 1);
            if (endPage - startPage + 1 < maxVisiblePages && startPage > 1) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }

            if (startPage > 1) {
                buttonsDiv.appendChild(createPaginationButton('1', 1));
                if (startPage > 2) {
                    const ellipsis = document.createElement('span');
                    ellipsis.className = 'px-3 py-2 text-gray-500';
                    ellipsis.textContent = '...';
                    buttonsDiv.appendChild(ellipsis);
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                const pageButton = createPaginationButton(i.toString(), i);
                if (i === pagination.currentPage) {
                    pageButton.classList.add('bg-indigo-600', 'text-white');
                    pageButton.classList.remove('bg-gray-200', 'hover:bg-gray-300');
                }
                buttonsDiv.appendChild(pageButton);
            }

            if (endPage < pagination.totalPages) {
                 if (endPage < pagination.totalPages - 1) {
                    const ellipsis = document.createElement('span');
                    ellipsis.className = 'px-3 py-2 text-gray-500';
                    ellipsis.textContent = '...';
                    buttonsDiv.appendChild(ellipsis);
                }
                buttonsDiv.appendChild(createPaginationButton(pagination.totalPages.toString(), pagination.totalPages));
            }

            // Next button
            if (pagination.currentPage < pagination.totalPages) {
                buttonsDiv.appendChild(createPaginationButton('Next', pagination.currentPage + 1));
            }

            paginationElement.appendChild(buttonsDiv);
        }

        // Helper function for pagination button creation

        function createPaginationButton(text, page) {
            const button = document.createElement('button');
            button.className = 'px-3 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors text-sm';
            button.textContent = text;
            button.addEventListener('click', function() {
                fetchReservations(page); // Corrected function call
                 // Scroll to top of the table with smooth animation
                 document.querySelector('.overflow-x-auto').scrollIntoView({ behavior: 'smooth' });
            });
            return button;
        }

        // Initial fetch on page load
        fetchReservations();
    });
</script>

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
    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); /* Adjusted for better responsiveness */
    gap: 1rem;
    padding: 1rem;
}

.pc-item {
    text-align: center;
    font-size: 0.875rem;
    transition: all 0.2s;
     box-shadow: 0 1px 3px rgba(0,0,0,0.05); /* Add a subtle shadow */
}

.pc-item:not(.reserved):hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1); /* Enhance shadow on hover */
}

.pc-item.reserved {
    background-color: #fee2e2;
    border-color: #fecaca;
    color: #991b1b;
    cursor: not-allowed;
    opacity: 0.8; /* Slightly less opaque */
}

.pc-item.selected {
    background-color: #e0e7ff;
    border-color: #818cf8;
    color: #4f46e5;
     box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Time slot styles */
.time-slot.selected {
    @apply bg-indigo-600 text-white ring-2 ring-indigo-300;
}

/* Responsive grid adjustment */
@media (min-width: 640px) {
    .grid-cols-auto-fill-sm {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    }
}

</style>

<?php require_once '../shared/footer.php'; ?>
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
$countQuery = "SELECT COUNT(*) as total FROM reservations r " . $whereClause;
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

// Fetch labs for displaying PC availability
$labsQuery = "SELECT * FROM labs";
$labsResult = $conn->query($labsQuery);
$labs = [];
while ($lab = $labsResult->fetch_assoc()) {
    $labs[] = $lab;
}

// Fetch active sit-in sessions by joining sit_in and pcs tables
$activeSitinQuery = "
    SELECT
        pc.lab_name,
        pc.pc_number,
        si.idno AS student_idno
    FROM
        sit_in si
    JOIN
        pcs pc ON si.lab = pc.lab_name
    WHERE
        si.out_time IS NULL"; // Assuming out_time is NULL for active sessions
$activeSitinResult = $conn->query($activeSitinQuery);
$activeSitinPCs = [];
if ($activeSitinResult) { // Check if query was successful
    while ($row = $activeSitinResult->fetch_assoc()) {
        $activeSitinPCs[$row['lab_name'] . '-' . $row['pc_number']] = $row['student_idno'];
    }
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
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Manage Reservations</h1>
                    <p class="text-lg text-gray-600">Review and manage lab reservations</p>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6 border border-gray-100" id="filterSection">
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
                        <tbody id="reservationsTableBody" class="divide-y divide-gray-200"><!-- Reservation data will be loaded here by JavaScript --></tbody>
                    </table>
                </div>
                <div id="pagination" class="p-6 flex justify-between items-center">

                </div>
            </div>

            <!-- PC Availability Section -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">PC Availability & Sit-in</h2>
                <?php if (empty($labs)): ?>
                    <p class="text-gray-500">No labs configured.</p>
                <?php else: ?>
                    <?php foreach ($labs as $lab): ?>
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-700 mb-3"><?php echo htmlspecialchars($lab['lab_name']); ?></h3>
                            <div class="grid grid-cols-auto-fill-sm gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <?php for ($i = 1; $i <= $lab['total_pcs']; $i++): ?>
                                    <?php
                                    $pcIdentifier = $lab['lab_name'] . '-' . $i;
                                    $isOccupied = isset($activeSitinPCs[$pcIdentifier]);
                                    $statusClass = $isOccupied ? 'bg-red-100 text-red-800 border-red-200 cursor-not-allowed' : 'bg-green-100 text-green-800 border-green-200 cursor-pointer hover:bg-green-200';
                                    $statusText = $isOccupied ? 'Occupied' : 'Available';
                                    $studentIdno = $isOccupied ? $activeSitinPCs[$pcIdentifier] : '';
                                    ?>
                                    <div class="flex flex-col items-center justify-center p-4 rounded-md border shadow-sm text-center <?php echo $statusClass; ?>"
                                         data-lab-name="<?php echo htmlspecialchars($lab['lab_name']); ?>"
                                         data-pc-number="<?php echo htmlspecialchars($i); ?>"
                                         <?php echo !$isOccupied ? 'onclick="openSitInModal(\'' . htmlspecialchars($lab['lab_name']) . '\', \'' . htmlspecialchars($i) . '\')"' : ''; ?>>
                                        <i class="fas fa-desktop text-2xl mb-2"></i>
                                        <span class="font-semibold">PC <?php echo $i; ?></span>
                                        <span class="text-xs"><?php echo $statusText; ?></span>
                                        <?php if ($isOccupied): ?>
                                            <span class="text-xs mt-1">Student ID: <?php echo htmlspecialchars($studentIdno); ?></span>
                                            <button class="mt-2 px-3 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600" onclick="event.stopPropagation(); endSitIn('<?php echo htmlspecialchars($pcIdentifier); ?>')">End Sit-in</button>
                                        <?php endif; ?>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sit-in Modal -->
        <div id="sitInModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Initiate Sit-in</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500" id="sitInModalDetails"></p>
                        <form id="sitInForm" class="mt-4">
                             <input type="hidden" id="modalLabName" name="lab_name">
                             <input type="hidden" id="modalPcNumber" name="pc_number">
                            <div class="mb-4">
                                <label for="studentIdno" class="block text-left text-sm font-medium text-gray-700">Student ID Number</label>
                                <input type="text" id="studentIdno" name="student_idno" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                            <div class="mb-4">
                                <label for="reason" class="block text-left text-sm font-medium text-gray-700">Reason for Sit-in</label>
                                <textarea id="reason" name="reason" rows="3" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                            </div>
                            <div class="items-center px-4 py-3">
                                <button type="submit" class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-300 mr-2">
                                    Start Sit-in
                                </button>
                                <button type="button" onclick="closeSitInModal()" class="px-4 py-2 bg-gray-400 text-white text-base font-medium rounded-md shadow-sm hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-300">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let currentPage = 1;

        // Function to open the sit-in modal
        window.openSitInModal = function(labName, pcNumber) {
            document.getElementById('modalLabName').value = labName;
            document.getElementById('modalPcNumber').value = pcNumber;
            document.getElementById('sitInModalDetails').textContent = `Initiate sit-in for Lab: ${labName}, PC Number: ${pcNumber}`;
            document.getElementById('sitInModal').classList.remove('hidden');
        }

        // Function to close the sit-in modal
        window.closeSitInModal = function() {
            document.getElementById('sitInModal').classList.add('hidden');
            document.getElementById('sitInForm').reset(); // Reset form fields
        }

        // Handle Sit-in Form Submission
        document.getElementById('sitInForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            initiateSitIn(formData);
        });

        // Function to initiate sit-in
        function initiateSitIn(formData) {
            fetch('sitting_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Sit-in initiated successfully!');
                    closeSitInModal();
                    // Refresh the PC availability section
                    location.reload(); // Simple reload for now, can optimize later
                } else {
                    alert('Error initiating sit-in: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while initiating sit-in.');
            });
        }

        // Function to end sit-in
        window.endSitIn = function(pcIdentifier) { // Changed parameter name to reflect the identifier
            if (confirm('Are you sure you want to end the sit-in for this PC?')) {
                // Split the identifier to get labName and pcNumber
                const [labName, pcNumber] = pcIdentifier.split('-');
                fetch('sit_in_end.php', { // Assuming sit_in_end.php handles ending sit-in
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    // Pass labName and pcNumber to the end sit-in script
                    body: 'lab_name=' + encodeURIComponent(labName) + '&pc_number=' + encodeURIComponent(pcNumber)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Sit-in ended successfully!');
                        // Refresh the PC availability section
                        location.reload(); // Simple reload for now
                    } else {
                        alert('Error ending sit-in: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while ending sit-in.');
                });
            }
        }

        // Function to fetch reservation data
        function fetchReservations(page = 1) {
            let statusFilter = document.querySelector('select[name="status"]').value;

            fetch(`fetch_reservations.php?page=${page}&status=${encodeURIComponent(statusFilter)}`) // Updated URL
                .then(response => response.json())
                .then(data => {
                    displayReservations(data.reservations);
                    createPagination(data.pagination);
                    attachActionListeners(); // Re-attach listeners after data load
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

        // Function to display reservations

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

                const reservationDate = new Date(reservation.reservation_date);
                const formattedDate = reservationDate.toLocaleDateString('en-US', {
                    month: 'short', day: 'numeric', year: 'numeric'
                });

                const statusClass = (reservation.status === 'pending') ? 'bg-yellow-100 text-yellow-800' :
                                    ((reservation.status === 'approved') ? 'bg-green-100 text-green-800' :
                                    ((reservation.status === 'rejected') ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'));

                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">${reservation.idno}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${reservation.full_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${reservation.lab_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${reservation.pc_number}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${reservation.time_slot}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${reservation.purpose}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                            ${reservation.status.charAt(0).toUpperCase() + reservation.status.slice(1)}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-left">
                        ${reservation.status === 'pending' ? `
                        <button class="accept-btn bg-green-500 text-white px-4 py-2 rounded-md text-xs mr-2" data-id="${reservation.reservation_id}">Accept</button>
                        <button class="reject-btn bg-red-500 text-white px-4 py-2 rounded-md text-xs" data-id="${reservation.reservation_id}">Reject</button>
                        ` : ''}
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Function to attach event listeners

        function attachActionListeners() {
            document.querySelectorAll('.accept-btn').forEach(button => {
                // Remove existing listeners before adding new ones
                button.removeEventListener('click', handleActionListener); // Prevent duplicate listeners
                button.addEventListener('click', handleActionListener);
            });
            document.querySelectorAll('.reject-btn').forEach(button => {
                button.removeEventListener('click', handleActionListener); // Prevent duplicate listeners
                button.addEventListener('click', handleActionListener);
            });
        }

        // Handler for action button clicks

        function handleActionListener() {
            const reservationId = this.getAttribute('data-id');
            const status = this.classList.contains('accept-btn') ? 'approved' : 'rejected';
            updateReservationStatus(reservationId, status);
        }
        // Function to update reservation status

        function updateReservationStatus(reservationId, status) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'update_reservation_status.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            // Refresh data after successful update
                            fetchReservations(currentPage); // Corrected function call
                        } else {
                            alert('Error updating reservation status: ' + response.message);
                        }
                    } catch (e) {
                        alert('Error parsing response from server: ' + xhr.responseText);
                    }
                } else {
                    alert('Error updating reservation status: Server returned status ' + xhr.status);
                }
            };

            xhr.onerror = function() {
                alert('Network error occurred while updating reservation status.');
            };

            // Send the POST request
            xhr.send('reservation_id=' + reservationId + '&status=' + status);
        }

        // Function to create pagination

        function createPagination(pagination) {
            const paginationElement = document.getElementById('pagination');
            paginationElement.innerHTML = ''; // Clear existing pagination

            if (pagination.totalPages <= 1) {
                return; // No pagination needed
            }

            currentPage = pagination.currentPage; // Update current page tracker

            // Create info text
            const infoDiv = document.createElement('div');
            infoDiv.className = 'text-sm text-gray-500';
            infoDiv.textContent = `Page ${pagination.currentPage} of ${pagination.totalPages} (${pagination.totalRecords} records)`;
            paginationElement.appendChild(infoDiv);

            // Create a container for the pagination buttons
            const buttonsDiv = document.createElement('div');
            buttonsDiv.className = 'flex space-x-2';

            // Previous button
            if (pagination.currentPage > 1) {
                buttonsDiv.appendChild(createPaginationButton('Prev', pagination.currentPage - 1));
            }

            // Page number buttons (simplified for brevity, can add ellipsis logic)
            const maxVisiblePages = 5;
            let startPage = Math.max(1, pagination.currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(pagination.totalPages, startPage + maxVisiblePages - 1);
            if (endPage - startPage + 1 < maxVisiblePages && startPage > 1) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }

            if (startPage > 1) {
                buttonsDiv.appendChild(createPaginationButton('1', 1));
                if (startPage > 2) {
                    const ellipsis = document.createElement('span');
                    ellipsis.className = 'px-3 py-2 text-gray-500';
                    ellipsis.textContent = '...';
                    buttonsDiv.appendChild(ellipsis);
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                const pageButton = createPaginationButton(i.toString(), i);
                if (i === pagination.currentPage) {
                    pageButton.classList.add('bg-indigo-600', 'text-white');
                    pageButton.classList.remove('bg-gray-200', 'hover:bg-gray-300');
                }
                buttonsDiv.appendChild(pageButton);
            }

            if (endPage < pagination.totalPages) {
                 if (endPage < pagination.totalPages - 1) {
                    const ellipsis = document.createElement('span');
                    ellipsis.className = 'px-3 py-2 text-gray-500';
                    ellipsis.textContent = '...';
                    buttonsDiv.appendChild(ellipsis);
                }
                buttonsDiv.appendChild(createPaginationButton(pagination.totalPages.toString(), pagination.totalPages));
            }

            // Next button
            if (pagination.currentPage < pagination.totalPages) {
                buttonsDiv.appendChild(createPaginationButton('Next', pagination.currentPage + 1));
            }

            paginationElement.appendChild(buttonsDiv);
        }

        // Helper function for pagination button creation

        function createPaginationButton(text, page) {
            const button = document.createElement('button');
            button.className = 'px-3 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors text-sm';
            button.textContent = text;
            button.addEventListener('click', function() {
                fetchReservations(page); // Corrected function call
                 // Scroll to top of the table with smooth animation
                 document.querySelector('.overflow-x-auto').scrollIntoView({ behavior: 'smooth' });
            });
            return button;
        }

        // Initial fetch on page load
        fetchReservations();
    });
</script>

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
    grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); /* Adjusted for better responsiveness */
    gap: 1rem;
    padding: 1rem;
}

.pc-item {
    text-align: center;
    font-size: 0.875rem;
    transition: all 0.2s;
     box-shadow: 0 1px 3px rgba(0,0,0,0.05); /* Add a subtle shadow */
}

.pc-item:not(.reserved):hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1); /* Enhance shadow on hover */
}

.pc-item.reserved {
    background-color: #fee2e2;
    border-color: #fecaca;
    color: #991b1b;
    cursor: not-allowed;
    opacity: 0.8; /* Slightly less opaque */
}

.pc-item.selected {
    background-color: #e0e7ff;
    border-color: #818cf8;
    color: #4f46e5;
     box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Time slot styles */
.time-slot.selected {
    @apply bg-indigo-600 text-white ring-2 ring-indigo-300;
}

/* Responsive grid adjustment */
@media (min-width: 640px) {
    .grid-cols-auto-fill-sm {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    }
}

</style>

<?php require_once '../shared/footer.php'; ?>
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
$countQuery = "SELECT COUNT(*) as total FROM reservations r " . $whereClause;
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

// Fetch labs for displaying PC availability
$labsQuery = "SELECT * FROM labs";
$labsResult = $conn->query($labsQuery);
$labs = [];
while ($lab = $labsResult->fetch_assoc()) {
    $labs[] = $lab;
}

// Fetch active sit-in sessions by joining sit_in and pcs tables
// Corrected query based on database schema
/*
$currentSitInQuery = "SELECT pc_id, student_idno FROM current_sitin";
$currentSitInResult = $conn->query($currentSitInQuery);
$currentSitIns = [];
while ($sitin = $currentSitInResult->fetch_assoc()) {
    $currentSitIns[$sitin['pc_id']] = $sitin['student_idno'];
}

*/
$activeSitinQuery = "
    SELECT
 pc.lab_name,
 pc.pc_number,
 si.idno AS student_idno
    FROM
 sit_in si
 JOIN
 pcs pc ON si.lab = pc.lab_name
    WHERE
 si.out_time IS NULL"; // Assuming out_time is NULL for active sessions
$activeSitinResult = $conn->query($activeSitinQuery);
$activeSitinPCs = [];
while ($row = $activeSitinResult->fetch_assoc()) {
    $activeSitinPCs[$row['lab_name'] . '-' . $row['pc_number']] = $row['student_idno'];
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
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Manage Reservations</h1>
                    <p class="text-lg text-gray-600">Review and manage lab reservations</p>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="bg-white rounded-xl shadow-md p-6 mb-6 border border-gray-100" id="filterSection">
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purpose</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="reservationsTableBody" class="divide-y divide-gray-200"><!-- Reservation data will be loaded here by JavaScript --></tbody>
                    </table>
                </div>
                <div id="pagination" class="p-6 flex justify-between items-center">

                </div>
            </div>

            <!-- PC Availability Section -->
            <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">PC Availability & Sit-in</h2>
                <?php if (empty($labs)): ?>
                    <p class="text-gray-500">No labs configured.</p>
                <?php else: ?>
                    <?php foreach ($labs as $lab): ?>
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-700 mb-3"><?php echo htmlspecialchars($lab['lab_name']); ?></h3>
                            <div class="grid grid-cols-auto-fill-sm gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <?php for ($i = 1; $i <= $lab['total_pcs']; $i++): ?>
                                    <?php
                                    $pcIdentifier = $lab['lab_name'] . '-' . $i;
                                    $isOccupied = isset($activeSitinPCs[$pcIdentifier]);
                                    $statusClass = $isOccupied ? 'bg-red-100 text-red-800 border-red-200 cursor-not-allowed' : 'bg-green-100 text-green-800 border-green-200 cursor-pointer hover:bg-green-200';
                                    $statusText = $isOccupied ? 'Occupied' : 'Available';
                                    $studentIdno = $isOccupied ? $activeSitinPCs[$pcIdentifier] : '';
                                    ?>
                                    <div class="flex flex-col items-center justify-center p-4 rounded-md border shadow-sm text-center <?php echo $statusClass; ?>"
                                         data-pc-id="<?php echo htmlspecialchars($pcId); ?>"
                                         data-lab-id="<?php echo htmlspecialchars($lab['id']); ?>"
                                         data-pc-number="<?php echo htmlspecialchars($i); ?>"
                                         <?php echo !$isOccupied ? 'onclick="openSitInModal(\'' . htmlspecialchars($pcId) . '\', \'' . htmlspecialchars($lab['lab_name']) . '\', \'' . htmlspecialchars($i) . '\')"' : ''; ?>>
                                        <i class="fas fa-desktop text-2xl mb-2"></i>
                                        <span class="font-semibold">PC <?php echo $i; ?></span>
                                        <span class="text-xs"><?php echo $statusText; ?></span>
                                        <?php if ($isOccupied): ?>
                                            <span class="text-xs mt-1">Student ID: <?php echo htmlspecialchars($studentIdno); ?></span>
                                            <button class="mt-2 px-3 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600" onclick="event.stopPropagation(); endSitIn('<?php echo htmlspecialchars($pcIdentifier); ?>')">End Sit-in</button>
                                        <?php endif; ?>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sit-in Modal -->
        <div id="sitInModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Initiate Sit-in</h3>
                    <div class="mt-2 px-7 py-3">
                        <p class="text-sm text-gray-500" id="sitInModalDetails"></p>
                        <form id="sitInForm" class="mt-4">
                            <input type="hidden" id="modalPcId" name="pc_id">
                            <div class="mb-4">
                                <label for="studentIdno" class="block text-left text-sm font-medium text-gray-700">Student ID Number</label>
                                <input type="text" id="studentIdno" name="student_idno" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                            <div class="mb-4">
                                <label for="reason" class="block text-left text-sm font-medium text-gray-700">Reason for Sit-in</label>
                                <textarea id="reason" name="reason" rows="3" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                            </div>
                            <div class="items-center px-4 py-3">
                                <button type="submit" class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-300 mr-2">
                                    Start Sit-in
                                </button>
                                <button type="button" onclick="closeSitInModal()" class="px-4 py-2 bg-gray-400 text-white text-base font-medium rounded-md shadow-sm hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-300">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </main>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let currentPage = 1;

        // Function to open the sit-in modal
        window.openSitInModal = function(pcId, labName, pcNumber) {
            document.getElementById('modalPcId').value = pcId;
            document.getElementById('sitInModalDetails').textContent = `Initiate sit-in for Lab: ${labName}, PC Number: ${pcNumber}`;
            document.getElementById('sitInModal').classList.remove('hidden');
        }

        // Function to close the sit-in modal
        window.closeSitInModal = function() {
            document.getElementById('sitInModal').classList.add('hidden');
            document.getElementById('sitInForm').reset(); // Reset form fields
        }

        // Handle Sit-in Form Submission
        document.getElementById('sitInForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            initiateSitIn(formData);
        });

        // Function to initiate sit-in
        function initiateSitIn(formData) {
            fetch('sitting_process.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Sit-in initiated successfully!');
                    closeSitInModal();
                    // Refresh the PC availability section
                    location.reload(); // Simple reload for now, can optimize later
                } else {
                    alert('Error initiating sit-in: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while initiating sit-in.');
            });
        }

        // Function to end sit-in
        window.endSitIn = function(pcId) {
            if (confirm('Are you sure you want to end the sit-in for this PC?')) {
                fetch('sit_in_end.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'pc_id=' + encodeURIComponent(pcId)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Sit-in ended successfully!');
                        // Refresh the PC availability section
                        location.reload(); // Simple reload for now
                    } else {
                        alert('Error ending sit-in: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while ending sit-in.');
                });
            }
        }

        // Function to fetch reservation data
        function fetchReservations(page = 1) {
            let statusFilter = document.querySelector('select[name="status"]').value;

            fetch(`fetch_reservations.php?page=${page}&status=${encodeURIComponent(statusFilter)}`) // Updated URL
                .then(response => response.json())
                .then(data => {
                    displayReservations(data.reservations);
                    createPagination(data.pagination);
                    attachActionListeners(); // Re-attach listeners after data load
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

        // Function to display reservations

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

                const reservationDate = new Date(reservation.reservation_date);
                const formattedDate = reservationDate.toLocaleDateString('en-US', {
                    month: 'short', day: 'numeric', year: 'numeric'
                });

                const statusClass = (reservation.status === 'pending') ? 'bg-yellow-100 text-yellow-800' :
                                    ((reservation.status === 'approved') ? 'bg-green-100 text-green-800' :
                                    ((reservation.status === 'rejected') ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'));

                row.innerHTML = `
                    <td class="px-6 py-4 whitespace-nowrap">${reservation.idno}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${reservation.full_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${reservation.lab_name}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${reservation.pc_number}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${reservation.time_slot}</td>
                    <td class="px-6 py-4 whitespace-nowrap">${reservation.purpose}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${statusClass}">
                            ${reservation.status.charAt(0).toUpperCase() + reservation.status.slice(1)}                            
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-left">
                        ${reservation.status === 'pending' ? `
                        <button class="accept-btn bg-green-500 text-white px-4 py-2 rounded-md text-xs mr-2" data-id="${reservation.reservation_id}">Accept</button>
                        <button class="reject-btn bg-red-500 text-white px-4 py-2 rounded-md text-xs" data-id="${reservation.reservation_id}">Reject</button>
                        ` : ''}
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Function to attach event listeners

        function attachActionListeners() {
            document.querySelectorAll('.accept-btn').forEach(button => {
                // Remove existing listeners before adding new ones
                button.removeEventListener('click', handleActionListener); // Prevent duplicate listeners
                button.addEventListener('click', handleActionListener);
            });
            document.querySelectorAll('.reject-btn').forEach(button => {
                button.removeEventListener('click', handleActionListener); // Prevent duplicate listeners
                button.addEventListener('click', handleActionListener);
            });
        }

        // Handler for action button clicks

        function handleActionListener() {
            const reservationId = this.getAttribute('data-id');
            const status = this.classList.contains('accept-btn') ? 'approved' : 'rejected';
            updateReservationStatus(reservationId, status);
        }
        // Function to update reservation status

        function updateReservationStatus(reservationId, status) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'update_reservation_status.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            // Refresh data after successful update
                            fetchData(currentPage); 
                        } else {
                            alert('Error updating reservation status: ' + response.message);
                        }
                    } catch (e) {
                        alert('Error parsing response from server: ' + xhr.responseText);
                    }
                } else {
                    alert('Error updating reservation status: Server returned status ' + xhr.status);
                }
            };
            
            xhr.onerror = function() {
                alert('Network error occurred while updating reservation status.');
            };

            // Send the POST request
            xhr.send('reservation_id=' + reservationId + '&status=' + status);
        }

        // Function to create pagination

        function createPagination(pagination) {
            const paginationElement = document.getElementById('pagination');
            paginationElement.innerHTML = ''; // Clear existing pagination

            if (pagination.totalPages <= 1) {
                return; // No pagination needed
            }

            currentPage = pagination.currentPage; // Update current page tracker

            // Create info text
            const infoDiv = document.createElement('div');
            infoDiv.className = 'text-sm text-gray-500';
            infoDiv.textContent = `Page ${pagination.currentPage} of ${pagination.totalPages} (${pagination.totalRecords} records)`;
                paginationElement.appendChild(infoDiv);

            // Create a container for the pagination buttons
            const buttonsDiv = document.createElement('div');
            buttonsDiv.className = 'flex space-x-2';

            // Previous button
            if (pagination.currentPage > 1) {
                buttonsDiv.appendChild(createPaginationButton('Prev', pagination.currentPage - 1));
            }

            // Page number buttons (simplified for brevity, can add ellipsis logic)
            const maxVisiblePages = 5;
            let startPage = Math.max(1, pagination.currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(pagination.totalPages, startPage + maxVisiblePages - 1);
            if (endPage - startPage + 1 < maxVisiblePages && startPage > 1) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }

            if (startPage > 1) {
                buttonsDiv.appendChild(createPaginationButton('1', 1));
                if (startPage > 2) {
                    const ellipsis = document.createElement('span');
                    ellipsis.className = 'px-3 py-2 text-gray-500';
                    ellipsis.textContent = '...';
                    buttonsDiv.appendChild(ellipsis);
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                const pageButton = createPaginationButton(i.toString(), i);
                if (i === pagination.currentPage) {
                    pageButton.classList.add('bg-indigo-600', 'text-white');
                    pageButton.classList.remove('bg-gray-200', 'hover:bg-gray-300');
                }
                buttonsDiv.appendChild(pageButton);
            }

            if (endPage < pagination.totalPages) {
                 if (endPage < pagination.totalPages - 1) {
                    const ellipsis = document.createElement('span');
                    ellipsis.className = 'px-3 py-2 text-gray-500';
                    ellipsis.textContent = '...';
                    buttonsDiv.appendChild(ellipsis);
                }
                buttonsDiv.appendChild(createPaginationButton(pagination.totalPages.toString(), pagination.totalPages));
            }

            // Next button
            if (pagination.currentPage < pagination.totalPages) {
                buttonsDiv.appendChild(createPaginationButton('Next', pagination.currentPage + 1));
            }

            paginationElement.appendChild(buttonsDiv);
        }

        // Helper function for pagination button creation

        function createPaginationButton(text, page) {
            const button = document.createElement('button');
            button.className = 'px-3 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors text-sm';
            button.textContent = text;
            button.addEventListener('click', function() {
                fetchData(page);
                 // Scroll to top of the table with smooth animation
                 document.querySelector('.overflow-x-auto').scrollIntoView({ behavior: 'smooth' });
            });
            return button;
        }
    });
    // Initial fetch on page load
    fetchReservations();
});
</script>

<?php require_once '../shared/footer.php'; ?>