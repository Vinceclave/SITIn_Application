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
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$idno = isset($user['idno']) ? $user['idno'] : '';

// Fetch sit-in history
$sitInQuery = "SELECT lab, reason, in_time, out_time FROM sit_in WHERE idno = ? ORDER BY sit_in_id ASC";
$sitInStmt = $conn->prepare($sitInQuery);
$sitInStmt->bind_param("s", $idno);
$sitInStmt->execute();
$sitInResult = $sitInStmt->get_result();
?>

<div>
    <?php include '../shared/aside.php'; ?>
    <main class="px-10 py-4">
        <h1 class="text-4xl font-bold mb-4">History</h1>
        <p class="mb-8">Welcome to your history, <span class="font-semibold"><?php echo htmlspecialchars($user['username']); ?></span>!</p>

        <!-- Display Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <p class="text-green-600"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <p class="text-red-600"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php endif; ?>

        <!-- Report Button (Opens Modal) -->
        <div class="mb-4">
            <button onclick="openModal()" class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-900 transition duration-200">
                Report Issue
            </button>
        </div>

        <!-- Sit-In History Table -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-semibold mb-4">Sit-In History</h2>

            <?php if ($sitInResult->num_rows > 0): ?>
                <div class="overflow-x-auto">
                    <table class="w-full bg-white border border-gray-200 rounded-lg shadow-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left font-medium text-gray-700 border-b">Lab</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-700 border-b">Reason</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-700 border-b">In Time</th>
                                <th class="px-6 py-3 text-left font-medium text-gray-700 border-b">Out Time</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700">
                            <?php while ($row = $sitInResult->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition duration-200">
                                    <td class="px-6 py-3 border-b"><?php echo htmlspecialchars($row['lab']); ?></td>
                                    <td class="px-6 py-3 border-b"><?php echo htmlspecialchars($row['reason']); ?></td>
                                    <td class="px-6 py-3 border-b"><?php echo htmlspecialchars($row['in_time']); ?></td>
                                    <td class="px-6 py-3 border-b"><?php echo htmlspecialchars($row['out_time']); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-600">No sit-in history found.</p>
            <?php endif; ?>
        </div>
    </main>
</div>

<!-- Feedback Modal -->
<div id="feedbackModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden flex items-center justify-center">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <h2 class="text-2xl font-semibold mb-4">Submit Feedback</h2>
        <form action="submit_feedback.php" method="POST">
            <input type="hidden" name="idno" value="<?php echo htmlspecialchars($idno); ?>">

            <label class="block text-gray-700 font-medium">Lab:</label>
            <input type="text" name="lab" class="w-full border rounded-lg px-3 py-2 mt-1 mb-3" required>

            <label class="block text-gray-700 font-medium">Message:</label>
            <textarea name="message" class="w-full border rounded-lg px-3 py-2 mt-1 mb-3" required></textarea>

            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Submit</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal() { document.getElementById("feedbackModal").classList.remove("hidden"); }
function closeModal() { document.getElementById("feedbackModal").classList.add("hidden"); }
</script>

<?php require_once '../shared/footer.php'; ?>
