<?php
session_start();
require_once '../config/config.php';
require_once '../shared/header.php';

if(isset($_SESSION['success'])): // Added success alert block
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
        'admin_name' => $row['admin_name']

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

?>
<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.5/dist/notiflix-aio-3.2.5.min.js"></script>

<div class="container max-w-[1400px] mx-auto mt-20 px-4 sm:px-6 md:px-8 lg:px-10">
    <?php include '../shared/aside.php'; ?>
    <main class="my-4">
        <section class="px-4 sm:px-6 md:px-8 py-6">
            <!-- Welcome Header -->
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl p-6 sm:p-8 md:p-10 text-white mb-8 shadow-lg">
                <div class="flex items-center gap-4">
                    <i class="fas fa-user-circle text-4xl sm:text-5xl opacity-90"></i>
                    <div>
                        <h1 class="text-3xl sm:text-4xl font-bold">
                            Welcome back, <span class="text-indigo-200"><?php echo htmlspecialchars($user['username']); ?></span>!
                        </h1>
                        <p class="text-indigo-100 mt-2">Manage your laboratory sessions and stay updated with announcements.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Announcements Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200/50 backdrop-blur-sm overflow-hidden">
                    <div class="border-b border-gray-200/50 px-6 py-4 flex items-center gap-3">
                        <i class="fas fa-bullhorn text-indigo-600"></i>
                        <h2 class="text-xl font-semibold">Announcements</h2>
                    </div>
                    <div class="p-6 overflow-y-auto max-h-[500px] space-y-4">
                        <?php if (empty($announcements)): ?>
                            <div class="text-center py-8">
                                <i class="fas fa-inbox text-4xl text-gray-400 mb-3"></i>
                                <p class="text-gray-500">No announcements available.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($announcements as $announcement): ?>
                                <div class="bg-gray-50 rounded-lg p-4 hover:shadow-md transition-all duration-200">
                                    <div class="flex items-center gap-2 mb-2">
                                        <i class="fas fa-circle-info text-indigo-600"></i>
                                    <div class="flex items-center text-sm text-gray-600 mb-2">
                                        <i class="fas fa-user mr-2"></i>
                                        <span><?php echo htmlspecialchars($announcement['admin_name']); ?></span>
                                        <i class="fas fa-calendar-alt mx-2"></i>
                                        <span><?php echo htmlspecialchars($announcement['date']); ?></span>
                                    </div>
                                     <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($announcement['message'])); ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Rules and Regulations Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200/50 backdrop-blur-sm overflow-hidden">
                    <div class="border-b border-gray-200/50 px-6 py-4 flex items-center gap-3">
                        <i class="fas fa-book text-indigo-600"></i>
                        <h2 class="text-xl font-semibold">Rules and Regulations</h2>
                    </div>
                    <div class="p-6 overflow-y-auto max-h-[500px] space-y-3">
                        <?php foreach ($rules as $index => $rule): ?>
                            <?php if ($index <= 2): ?>
                                <h3 class="text-lg font-semibold text-indigo-600"><?php echo htmlspecialchars($rule); ?></h3>
                            <?php else: ?>
                                <div class="flex items-start gap-3 hover:bg-gray-50 p-2 rounded-lg transition-colors">
                                    <?php if (strpos($rule, '.') !== false): ?>
                                        <i class="fas fa-check-circle text-indigo-600 mt-1"></i>
                                    <?php else: ?>
                                        <i class="fas fa-arrow-right text-gray-400 mt-1"></i>
                                    <?php endif; ?>
                                    <p class="text-gray-700"><?php echo htmlspecialchars($rule); ?></p>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </section>
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
</style>

<?php require_once '../shared/footer.php'; ?>
