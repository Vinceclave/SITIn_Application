<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/config.php';
require_once '../shared/header.php';

// Handle manual update if requested
$updateMessage = '';
if (isset($_POST['update_leaderboard'])) {
    try {
        // Update the leaderboard table with current data
        $updateQuery = "INSERT INTO leaderboard (idno, full_name, total_sessions, total_points, last_updated)
                        SELECT 
                            s.idno,
                            s.full_name,
                            COUNT(DISTINCT s.sit_in_id) as total_sessions,
                            COALESCE(SUM(lp.points), 0) as total_points,
                            NOW() as last_updated
                        FROM sit_in s
                        LEFT JOIN lab_points lp ON s.sit_in_id = lp.sit_in_id
                        GROUP BY s.idno, s.full_name
                        ON DUPLICATE KEY UPDATE
                            full_name = VALUES(full_name),
                            total_sessions = VALUES(total_sessions),
                            total_points = VALUES(total_points),
                            last_updated = VALUES(last_updated)";
        
        $stmt = $conn->prepare($updateQuery);
        $stmt->execute();
        
        $affectedRows = $stmt->rowCount();
        $updateMessage = "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4' role='alert'>
                            <strong class='font-bold'>Success!</strong>
                            <span class='block sm:inline'> Leaderboard updated successfully. Affected rows: $affectedRows</span>
                        </div>";
    } catch (PDOException $e) {
        $updateMessage = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4' role='alert'>
                            <strong class='font-bold'>Error!</strong>
                            <span class='block sm:inline'> " . $e->getMessage() . "</span>
                        </div>";
    }
}

// Get current leaderboard data
$leaderboardQuery = "SELECT * FROM leaderboard ORDER BY total_points DESC, total_sessions DESC LIMIT 20";
$leaderboardStmt = $conn->prepare($leaderboardQuery);
$leaderboardStmt->execute();
$leaderboardData = $leaderboardStmt->fetchAll(PDO::FETCH_ASSOC);

// Get last update time
$lastUpdateQuery = "SELECT MAX(last_updated) as last_update FROM leaderboard";
$lastUpdateStmt = $conn->prepare($lastUpdateQuery);
$lastUpdateStmt->execute();
$lastUpdateResult = $lastUpdateStmt->fetch(PDO::FETCH_ASSOC);
$lastUpdate = $lastUpdateResult['last_update'] ? date('F j, Y, g:i a', strtotime($lastUpdateResult['last_update'])) : 'Never';
?>

<div class="flex min-h-screen bg-gray-50 text-gray-900 pb-14">
    <?php include '../shared/aside.php'; ?>
    <main class="flex-1 p-4 ml-64">
        <div class="max-w-[1400px] mx-auto">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-semibold text-gray-800">Manage Leaderboard</h1>
                    <p class="text-lg text-gray-600">Update and view the student leaderboard</p>
                </div>
            </div>

            <?php echo $updateMessage; ?>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Leaderboard Management</h3>
                    <div class="text-sm text-gray-500">
                        Last updated: <?php echo $lastUpdate; ?>
                    </div>
                </div>
                
                <div class="mb-6">
                    <form method="post" action="">
                        <button type="submit" name="update_leaderboard" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-sync-alt mr-2"></i>Update Leaderboard Now
                        </button>
                    </form>
                </div>
                
                <div class="mb-4">
                    <h4 class="text-md font-medium text-gray-700 mb-2">Current Leaderboard Data</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Points</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sessions</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Updated</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if (count($leaderboardData) > 0): ?>
                                    <?php foreach ($leaderboardData as $index => $student): ?>
                                        <tr class="<?php echo $index < 3 ? 'bg-gray-50' : ''; ?>">
                                            <td class="px-4 py-3 text-sm text-gray-900"><?php echo $index + 1; ?></td>
                                            <td class="px-4 py-3 text-sm text-gray-900"><?php echo htmlspecialchars($student['idno']); ?></td>
                                            <td class="px-4 py-3 text-sm text-gray-900"><?php echo htmlspecialchars($student['full_name']); ?></td>
                                            <td class="px-4 py-3 text-sm text-gray-900"><?php echo $student['total_points']; ?></td>
                                            <td class="px-4 py-3 text-sm text-gray-900"><?php echo $student['total_sessions']; ?></td>
                                            <td class="px-4 py-3 text-sm text-gray-900"><?php echo date('M d, Y H:i', strtotime($student['last_updated'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="px-4 py-4 text-center text-gray-500">No leaderboard data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <h4 class="text-md font-medium text-gray-700 mb-2">About the Leaderboard</h4>
                    <p class="text-sm text-gray-600 mb-2">
                        The leaderboard tracks student participation in sit-in sessions and points earned.
                        It is automatically updated when viewing the reports page, but you can also update it manually here.
                    </p>
                    <p class="text-sm text-gray-600">
                        For automated updates, you can set up a cron job to run the update_leaderboard_cron.php script periodically.
                        Example: <code class="bg-gray-200 px-2 py-1 rounded">0 * * * * php /path/to/update_leaderboard_cron.php</code>
                    </p>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once '../shared/footer.php'; ?> 