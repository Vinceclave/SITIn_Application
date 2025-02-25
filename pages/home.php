<?php
session_start();
require_once '../config/config.php';
require_once '../shared/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM students WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Ensure role is set
$role = isset($user['role']) ? $user['role'] : 'student';

// Fetch announcements and rules from a static file or database
$announcements = [
    [
        'title' => 'Announcement',
        'date' => '2025-Feb-25',
        'content' => 'UC did it again.',
        'author' => 'CCS Admin'
    ],
    [
        'title' => 'Announcement',
        'date' => '2025-Feb-03',
        'content' => 'The College of Computer Studies will open the registration of students for the Sit-in privilege starting tomorrow. Thank you! Lab Supervisor',
        'author' => 'CCS Admin'
    ],
    [
        'title' => 'Important Announcement',
        'date' => '2024-May-08',
        'content' => 'We are excited to announce the launch of our new website! 🎉 Explore our latest products and services now!',
        'author' => 'CCS Admin'
    ]
];

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
    <?php include '../shared/aside.php'; ?>
    <main class="pl-72 p-4">

    
        <section class="px-10 py-4">
            <h1 class="text-4xl font-bold mb-4">Home</h1>
            <p class="mb-8">Welcome to the Home page, <?php echo htmlspecialchars($user['username']); ?>!</p>
            <!-- Home-specific content -->
            <div class="flex space-x-8">
                <div class="flex-1">
                    <h2 class="text-2xl font-semibold mb-2">Announcements</h2>
                    <div class="overflow-y-auto max-h-96 p-4 bg-gray-100 rounded-lg shadow-md">
                        <?php foreach ($announcements as $announcement): ?>
                            <div class="mb-4 p-2 border-b border-gray-300">
                                <strong class="block text-lg"><?php echo htmlspecialchars($announcement['title']); ?></strong>
                                <span class="text-sm text-gray-600"><?php echo htmlspecialchars($announcement['author']); ?> | <?php echo htmlspecialchars($announcement['date']); ?></span>
                                <p class="mt-2"><?php echo htmlspecialchars($announcement['content']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>  
                <div class="flex-1">
                    <h2 class="text-2xl font-semibold mb-2">Rules and Regulations</h2>
                    <div class="overflow-y-auto max-h-96 p-4 bg-gray-100 rounded-lg shadow-md">
                        <?php foreach ($rules as $rule): ?>
                            <p class="mb-2"><?php echo htmlspecialchars($rule); ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>  
            </div>
        </section>
    </main>

<?php
require_once '../shared/footer.php';
?>
