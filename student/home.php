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

// Ensure role is set
$role = isset($user['role']) ? $user['role'] : 'student';

// Fetch announcements from database
$query = "SELECT * FROM announcements ORDER BY date DESC";
$result = mysqli_query($conn, $query);
$announcements = [];
while ($row = mysqli_fetch_assoc($result)) {
    $announcements[] = [
        'message' => $row['message'],
        'date' => date('Y-M-d', strtotime($row['date'])),
        'admin_name' => isset($row['admin_name']) ? $row['admin_name'] : 'Admin'
    ];
}

$rules = [
    'University of Cebu COLLEGE OF INFORMATION & COMPUTER STUDIES',
    'LABORATORY RULES AND REGULATIONS',
    'To avoid embarrassment and maintain camaraderie with your friends and superiors at our laboratories, please observe the following:',
    '1. Maintain silence, proper decorum, and discipline inside the laboratory. Mobile phones, walkmans and other personal pieces of equipment must be switched off.',
    '2. Games are not allowed inside the lab. This includes computer-related games, card games and other games that may disturb the operation of the lab.',
    '3. Surfing the Internet is allowed only with the permission of the instructor. Downloading and installing of software are strictly prohibited.',
    '4. Getting access to other websites not related to the course (especially pornographic and illicit sites) is strictly prohibited.',
    '5. Deleting computer files and changing the set-up of the computer is a major offense.',
    '6. Observe computer time usage carefully. A fifteen-minute allowance is given for each use. Otherwise, the unit will be given to those who wish to "sit-in".',
    '7. Observe proper decorum while inside the laboratory.',
    'Do not get inside the lab unless the instructor is present.',
    'All bags, knapsacks, and the likes must be deposited at the counter.',
    'Follow the seating arrangement of your instructor.',
    'At the end of class, all software programs must be closed.',
    'Return all chairs to their proper places after using.',
    '8. Chewing gum, eating, drinking, smoking, and other forms of vandalism are prohibited inside the lab.',
    '9. Anyone causing a continual disturbance will be asked to leave the lab. Acts or gestures offensive to the members of the community, including public display of physical intimacy, are not tolerated.',
    '10. Persons exhibiting hostile or threatening behavior such as yelling, swearing, or disregarding requests made by lab personnel will be asked to leave the lab.',
    '11. For serious offense, the lab personnel may call the Civil Security Office (CSU) for assistance.',
    '12. Any technical problem or difficulty must be addressed to the laboratory supervisor, student assistant or instructor immediately.',
    'DISCIPLINARY ACTION',
    'First Offense - The Head or the Dean or OIC recommends to the Guidance Center for a suspension from classes for each offender.',
    'Second and Subsequent Offenses - A recommendation for a heavier sanction will be endorsed to the Guidance Center'
];

// Get upcoming sessions
$current_date = date('Y-m-d');
$idno = $user['idno']; // Get the student's ID number
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
?>
<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.5/dist/notiflix-aio-3.2.5.min.js"></script>

<div class="flex min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 text-gray-900 pb-14">
    <?php include '../shared/aside.php'; ?>
    <main class="flex-1 p-6 pt-24">
        <div class="max-w-7xl mx-auto">
            <!-- Welcome Section -->
            <div class="bg-white bg-opacity-80 backdrop-blur-sm rounded-xl shadow-md p-6 mb-8 border border-gray-100">
                <div class="flex items-center space-x-4">
                    <div class="bg-indigo-100 p-3 rounded-full">
                        <i class="fas fa-user text-2xl text-indigo-600"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Student Home</h1>
                        <p class="text-lg text-gray-600">Welcome, <span class="font-medium text-indigo-600"><?php echo htmlspecialchars($user['username']); ?></span>!</p>
                    </div>
                </div>
            </div>

            <!-- Quick Links Section -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <a href="reservation.php" class="bg-gradient-to-br from-indigo-500 to-indigo-600 text-white p-6 rounded-xl shadow-md hover:shadow-xl transform hover:scale-105 transition duration-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-indigo-100">Make Reservation</h2>
                        <div class="bg-white/20 p-2 rounded-lg">
                            <i class="fas fa-calendar-plus text-xl"></i>
                        </div>
                    </div>
                    <p class="text-indigo-200 text-sm mt-3">Reserve a lab session</p>
                </a>
                
                <a href="history.php" class="bg-gradient-to-br from-emerald-500 to-emerald-600 text-white p-6 rounded-xl shadow-md hover:shadow-xl transform hover:scale-105 transition duration-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-emerald-100">Session History</h2>
                        <div class="bg-white/20 p-2 rounded-lg">
                            <i class="fas fa-history text-xl"></i>
                        </div>
                    </div>
                    <p class="text-emerald-200 text-sm mt-3">View past sessions</p>
                </a>
                
                <a href="profile.php" class="bg-gradient-to-br from-amber-500 to-amber-600 text-white p-6 rounded-xl shadow-md hover:shadow-xl transform hover:scale-105 transition duration-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-amber-100">Profile</h2>
                        <div class="bg-white/20 p-2 rounded-lg">
                            <i class="fas fa-user-edit text-xl"></i>
                        </div>
                    </div>
                    <p class="text-amber-200 text-sm mt-3">Update your information</p>
                </a>
                
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 rounded-xl shadow-md hover:shadow-xl transform hover:scale-105 transition duration-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-blue-100">Help</h2>
                        <div class="bg-white/20 p-2 rounded-lg">
                            <i class="fas fa-question-circle text-xl"></i>
                        </div>
                    </div>
                    <p class="text-blue-200 text-sm mt-3">Support and resources</p>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <!-- Announcements Section -->
                <div class="bg-white p-6 rounded-xl shadow-md lg:col-span-2 border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-bullhorn text-amber-500 mr-2"></i>
                        Announcements
                    </h2>
                    <div class="overflow-y-auto max-h-[400px] space-y-4 pr-2">
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
                                                <?php echo isset($announcement['admin_name']) ? htmlspecialchars($announcement['admin_name']) : 'Admin'; ?>
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
                
                <!-- Quick Actions -->
                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-calendar text-indigo-600 mr-2"></i>
                        Upcoming Sessions
                    </h2>
                    <div class="space-y-4">
                        <?php if (empty($upcoming_sessions)): ?>
                            <div class="text-center py-8">
                                <i class="fas fa-calendar-xmark text-4xl text-gray-400 mb-3"></i>
                                <p class="text-gray-500">No upcoming reservations.</p>
                                <a href="reservation.php" class="mt-3 inline-block bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                                    Make a Reservation
                                </a>
                            </div>
                        <?php else: 
                            foreach ($upcoming_sessions as $session): ?>
                            <div class="flex items-start p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                                <div class="bg-indigo-100 p-2 rounded-lg mr-4">
                                    <i class="fas fa-calendar-day text-indigo-600"></i>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-medium text-gray-800"><?php echo htmlspecialchars($session['lab_name']); ?></h3>
                                    <p class="text-sm text-gray-500">
                                        <i class="fas fa-calendar-alt mr-1"></i>
                                        <?php echo date('F d, Y', strtotime($session['reservation_date'])); ?>
                                        <i class="fas fa-clock ml-2 mr-1"></i>
                                        <?php echo htmlspecialchars($session['time_slot']); ?>
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        <i class="fas fa-desktop mr-1"></i>
                                        PC #<?php echo htmlspecialchars($session['pc_number']); ?>
                                    </p>
                                    <?php if($session['status'] == 'pending'): ?>
                                        <div class="mt-2">
                                            <span class="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">
                                                Pending Approval
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <div class="mt-2">
                                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                                Approved
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach;
                        endif; ?>
                    </div>
                </div>
            </div>

            <!-- Lab Rules Section -->
            <div class="bg-white p-6 rounded-xl shadow-md mb-8 border border-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-book text-blue-500 mr-2"></i>
                        Laboratory Rules and Regulations
                    </h2>
                </div>
                <div class="overflow-y-auto max-h-[400px] pr-2 space-y-4">
                    <?php foreach ($rules as $index => $rule): ?>
                        <?php if ($index <= 2): ?>
                            <h3 class="text-lg font-semibold text-indigo-600 mb-3"><?php echo htmlspecialchars($rule); ?></h3>
                        <?php else: ?>
                            <div class="flex items-start gap-4 hover:bg-gray-50 p-3 rounded-lg transition-colors">
                                <?php if (strpos($rule, '.') !== false): ?>
                                    <div class="bg-blue-100 p-1 rounded-full">
                                        <i class="fas fa-check-circle text-blue-600"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="bg-gray-100 p-1 rounded-full">
                                        <i class="fas fa-arrow-right text-gray-500"></i>
                                    </div>
                                <?php endif; ?>
                                <p class="text-gray-700"><?php echo htmlspecialchars($rule); ?></p>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
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
