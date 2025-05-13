<?php
session_start();
require_once '../config/config.php';
require_once '../shared/header.php';

if(isset($_SESSION['success'])):
?>
<script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.5/dist/notiflix-aio-3.2.5.min.js"></script>
<script>
    Notiflix.Notify.success("<?php echo addslashes($_SESSION['success']); ?>");
</script>
<?php unset($_SESSION['success']); endif; ?>

<?php
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

// Ensure role is set correctly
$role = isset($user['role']) ? $user['role'] : 'student';

// Fetch user's recent activity - session reservations
$idno = $user['idno']; // Get the student's ID number
$query = "SELECT r.*, l.lab_name FROM reservations r 
          JOIN labs l ON r.lab_name = l.lab_name
          WHERE r.idno = ? 
          ORDER BY r.reservation_date DESC, r.time_slot DESC 
          LIMIT 5";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $idno);
$stmt->execute();
$result = $stmt->get_result();
$recent_sessions = [];
while ($row = $result->fetch_assoc()) {
    $recent_sessions[] = $row;
}

// Fetch quick stats
$query = "SELECT COUNT(*) as total_sessions FROM reservations WHERE idno = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $idno);
$stmt->execute();
$result = $stmt->get_result();
$stats = $result->fetch_assoc();
$total_sessions = $stats['total_sessions'];

// Fetch upcoming reservations
$current_date = date('Y-m-d');
$query = "SELECT r.*, l.lab_name FROM reservations r 
          JOIN labs l ON r.lab_name = l.lab_name
          WHERE r.idno = ? AND (r.reservation_date > ? OR (r.reservation_date = ? AND r.status = 'pending')) 
          ORDER BY r.reservation_date ASC, r.time_slot ASC 
          LIMIT 3";
$stmt = $conn->prepare($query);
$stmt->bind_param("iss", $idno, $current_date, $current_date);
$stmt->execute();
$result = $stmt->get_result();
$upcoming_sessions = [];
while ($row = $result->fetch_assoc()) {
    $upcoming_sessions[] = $row;
}

// Fetch announcements
$query = "SELECT * FROM announcements ORDER BY date DESC LIMIT 5";
$result = mysqli_query($conn, $query);
$announcements = [];
while ($row = mysqli_fetch_assoc($result)) {
    $announcements[] = [
        'message' => $row['message'],
        'date' => date('F d, Y g:i A', strtotime($row['date'])),
        'admin_name' => $row['admin_name'] ?? 'Admin'
    ];
}
?>

<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<div class="flex min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 text-gray-900 pb-14">
    <?php include '../shared/aside.php'; ?>
    <main class="flex-1 p-6 pt-24">
        <div class="max-w-7xl mx-auto">
            <!-- Welcome Header -->
            <div class="bg-white bg-opacity-80 backdrop-blur-sm rounded-xl shadow-md p-6 mb-8 border border-gray-100">
                <div class="flex items-center space-x-4">
                    <div class="bg-indigo-100 p-3 rounded-full">
                        <i class="fas fa-tachometer-alt text-2xl text-indigo-600"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Student Dashboard</h1>
                        <p class="text-lg text-gray-600">Welcome, <span class="font-medium text-indigo-600"><?php echo htmlspecialchars($user['username']); ?></span>!</p>
                    </div>
                </div>
            </div>

            <!-- Stats Cards Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <!-- Total Sessions Card -->
                <div class="bg-white rounded-xl shadow-md border border-gray-200/50 p-6 flex items-center hover:shadow-lg transition-shadow duration-200">
                    <div class="bg-blue-100 rounded-full p-4 mr-4">
                        <i class="fas fa-calendar-check text-2xl text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="text-gray-500 text-sm font-medium">Your Sessions</h3>
                        <p class="text-2xl font-bold"><?php echo $total_sessions; ?></p>
                    </div>
                </div>
                
                <!-- Last Login Card -->
                <div class="bg-white rounded-xl shadow-md border border-gray-200/50 p-6 flex items-center hover:shadow-lg transition-shadow duration-200">
                    <div class="bg-teal-100 rounded-full p-4 mr-4">
                        <i class="fas fa-clock text-2xl text-teal-600"></i>
                    </div>
                    <div>
                        <h3 class="text-gray-500 text-sm font-medium">Current Date</h3>
                        <p class="text-2xl font-bold"><?php echo date('M d, Y'); ?></p>
                    </div>
                </div>
                
                <!-- Account Status Card -->
                <div class="bg-white rounded-xl shadow-md border border-gray-200/50 p-6 flex items-center hover:shadow-lg transition-shadow duration-200">
                    <div class="bg-green-100 rounded-full p-4 mr-4">
                        <i class="fas fa-user-check text-2xl text-green-600"></i>
                    </div>
                    <div>
                        <h3 class="text-gray-500 text-sm font-medium">Account Status</h3>
                        <p class="text-2xl font-bold text-green-600">Active</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <!-- Recent Activity Card -->
                <div class="bg-white p-6 rounded-xl shadow-md lg:col-span-2 border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-history text-blue-600 mr-2"></i>
                        Recent Activity
                    </h2>
                    <div class="overflow-y-auto max-h-[350px] space-y-4 pr-2">
                        <?php if (empty($recent_sessions)): ?>
                            <div class="text-center py-8">
                                <i class="fas fa-hourglass-empty text-4xl text-gray-400 mb-3"></i>
                                <p class="text-gray-500">No recent activity found.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recent_sessions as $session): ?>
                                <div class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <i class="fas fa-desktop text-blue-600"></i>
                                            <span class="font-medium"><?php echo htmlspecialchars($session['lab_name']); ?></span>
                                        </div>
                                        <span class="bg-<?php echo $session['status'] == 'approved' ? 'green' : ($session['status'] == 'pending' ? 'yellow' : 'red'); ?>-100 text-<?php echo $session['status'] == 'approved' ? 'green' : ($session['status'] == 'pending' ? 'yellow' : 'red'); ?>-800 text-xs px-2 py-1 rounded-full">
                                            <?php echo ucfirst(htmlspecialchars($session['status'])); ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600 mb-2">
                                        <i class="fas fa-calendar-alt mr-2"></i>
                                        <span><?php echo date('F d, Y', strtotime($session['reservation_date'])); ?></span>
                                        <i class="fas fa-clock mx-2"></i>
                                        <span>Time Slot: <?php echo htmlspecialchars($session['time_slot']); ?></span>
                                    </div>
                                    <div class="flex items-center text-sm text-gray-600">
                                        <i class="fas fa-desktop mr-2"></i>
                                        <span>PC #<?php echo htmlspecialchars($session['pc_number']); ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Announcements Card -->
                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-bullhorn text-amber-500 mr-2"></i>
                        Announcements
                    </h2>
                    <div class="overflow-y-auto max-h-[350px] space-y-4 pr-2">
                        <?php if (empty($announcements)): ?>
                            <div class="text-center py-8">
                                <i class="fas fa-inbox text-4xl text-gray-400 mb-3"></i>
                                <p class="text-gray-500">No announcements available.</p>
                            </div>
                        <?php else: 
                            foreach ($announcements as $announcement): ?>
                            <div class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="flex items-start">
                                    <div class="bg-amber-100 p-2 rounded-full mr-3">
                                        <i class="fas fa-bullhorn text-amber-600"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-gray-800"><?php echo nl2br(htmlspecialchars($announcement['message'])); ?></p>
                                        <div class="flex items-center justify-between mt-2 text-sm">
                                            <span class="text-gray-500">
                                                <i class="fas fa-user-shield mr-1"></i>
                                                <?php echo htmlspecialchars($announcement['admin_name']); ?>
                                            </span>
                                            <span class="text-gray-500">
                                                <i class="far fa-calendar-alt mr-1"></i>
                                                <?php echo htmlspecialchars($announcement['date']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach;
                        endif; ?>
                    </div>
                </div>
            </div>

            <!-- Upcoming Sessions Section -->
            <div class="bg-white p-6 rounded-xl shadow-md mb-8 border border-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-calendar-alt text-indigo-600 mr-2"></i>
                        Upcoming Reservations
                    </h2>
                    <a href="reservation.php" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center">
                        <span>Make Reservation</span>
                        <i class="fas fa-chevron-right ml-1 text-xs"></i>
                    </a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php if (empty($upcoming_sessions)): ?>
                        <div class="md:col-span-3 text-center py-8">
                            <i class="fas fa-calendar-xmark text-4xl text-gray-400 mb-3"></i>
                            <p class="text-gray-500">No upcoming reservations.</p>
                            <a href="reservation.php" class="mt-3 inline-block bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                                Make a Reservation
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($upcoming_sessions as $session): ?>
                            <div class="bg-gray-50 rounded-lg p-5 hover:shadow-md transition-shadow duration-200 border border-gray-100">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-building text-indigo-600"></i>
                                        <span class="font-medium text-lg"><?php echo htmlspecialchars($session['lab_name']); ?></span>
                                    </div>
                                    <span class="bg-<?php echo $session['status'] == 'approved' ? 'green' : 'yellow'; ?>-100 text-<?php echo $session['status'] == 'approved' ? 'green' : 'yellow'; ?>-800 text-xs px-2 py-1 rounded-full">
                                        <?php echo ucfirst(htmlspecialchars($session['status'])); ?>
                                    </span>
                                </div>
                                <div class="space-y-2 text-gray-600">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar-day mr-2 w-5"></i>
                                        <span><?php echo date('F d, Y', strtotime($session['reservation_date'])); ?></span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-clock mr-2 w-5"></i>
                                        <span><?php echo htmlspecialchars($session['time_slot']); ?></span>
                                    </div>
                                    <div class="flex items-center">
                                        <i class="fas fa-desktop mr-2 w-5"></i>
                                        <span>PC #<?php echo htmlspecialchars($session['pc_number']); ?></span>
                                    </div>
                                </div>
                                <?php if($session['status'] == 'pending'): ?>
                                    <div class="mt-4 flex justify-end">
                                        <a href="cancel_reservation.php?id=<?php echo $session['reservation_id']; ?>" class="text-red-500 hover:text-red-700 flex items-center text-sm">
                                            <i class="fas fa-times-circle mr-1"></i> Cancel
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Links Section -->
            <div class="bg-white p-6 rounded-xl shadow-md mb-8 border border-gray-100">
                <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-link text-blue-500 mr-2"></i>
                    Quick Links
                </h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                    <a href="reservation.php" class="flex flex-col items-center bg-blue-50 p-4 rounded-lg hover:bg-blue-100 transition-colors">
                        <i class="fas fa-calendar-plus text-2xl text-blue-600 mb-2"></i>
                        <span class="text-center text-sm">New Reservation</span>
                    </a>
                    <a href="history.php" class="flex flex-col items-center bg-purple-50 p-4 rounded-lg hover:bg-purple-100 transition-colors">
                        <i class="fas fa-history text-2xl text-purple-600 mb-2"></i>
                        <span class="text-center text-sm">View History</span>
                    </a>
                    <a href="profile.php" class="flex flex-col items-center bg-green-50 p-4 rounded-lg hover:bg-green-100 transition-colors">
                        <i class="fas fa-user-edit text-2xl text-green-600 mb-2"></i>
                        <span class="text-center text-sm">Edit Profile</span>
                    </a>
                    <a href="#" class="flex flex-col items-center bg-yellow-50 p-4 rounded-lg hover:bg-yellow-100 transition-colors">
                        <i class="fas fa-question-circle text-2xl text-yellow-600 mb-2"></i>
                        <span class="text-center text-sm">Help Center</span>
                    </a>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
/* Custom scrollbar for webkit browsers */
::-webkit-scrollbar {
    width: 8px;
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

/* Smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

.hover\:shadow-lg:hover {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
}
</style>

<?php require_once '../shared/footer.php'; ?>
