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

<?php if(isset($_SESSION['success'])): ?>
<script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.5/dist/notiflix-aio-3.2.5.min.js"></script>
<script>
    Notiflix.Notify.success("<?php echo addslashes($_SESSION['success']); ?>");
</script>
<?php unset($_SESSION['success']); endif; ?>

<?php if(isset($_SESSION['error'])): ?>
<script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.5/dist/notiflix-aio-3.2.5.min.js"></script>
<script>
    Notiflix.Notify.failure("<?php echo addslashes($_SESSION['error']); ?>");
</script>
<?php unset($_SESSION['error']); endif; ?>

<div class="flex min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 text-gray-900 pb-14">
    <?php include '../shared/aside.php'; ?>
    <main class="flex-1 p-6 pt-24">
        <div class="max-w-7xl mx-auto">
            <!-- Welcome Section -->
            <div class="bg-white bg-opacity-80 backdrop-blur-sm rounded-xl shadow-md p-6 mb-8 border border-gray-100">
                <div class="flex items-center space-x-4">
                    <div class="bg-indigo-100 p-3 rounded-full">
                        <i class="fas fa-history text-2xl text-indigo-600"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Lab Session History</h1>
                        <p class="text-lg text-gray-600">Welcome, <span class="font-medium text-indigo-600"><?php echo htmlspecialchars($user['username']); ?></span>!</p>
                    </div>
                </div>
            </div>

            <!-- Stats Section -->
            <?php
            // Get total sessions count
            $totalSessionsQuery = "SELECT COUNT(*) as total FROM sit_in WHERE idno = ?";
            $totalSessionsStmt = $conn->prepare($totalSessionsQuery);
            $totalSessionsStmt->bind_param("s", $idno);
            $totalSessionsStmt->execute();
            $totalSessions = $totalSessionsStmt->get_result()->fetch_assoc()['total'];

            // Get total labs visited
            $totalLabsQuery = "SELECT COUNT(DISTINCT lab) as total FROM sit_in WHERE idno = ?";
            $totalLabsStmt = $conn->prepare($totalLabsQuery);
            $totalLabsStmt->bind_param("s", $idno);
            $totalLabsStmt->execute();
            $totalLabs = $totalLabsStmt->get_result()->fetch_assoc()['total'];

            // Get total feedback submitted
            $totalFeedbackQuery = "SELECT COUNT(*) as total FROM feedback WHERE idno = ?";
            $totalFeedbackStmt = $conn->prepare($totalFeedbackQuery);
            $totalFeedbackStmt->bind_param("s", $idno);
            $totalFeedbackStmt->execute();
            $totalFeedback = $totalFeedbackStmt->get_result()->fetch_assoc()['total'];
            ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 text-white p-6 rounded-xl shadow-md hover:shadow-xl transform hover:scale-105 transition duration-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-indigo-100">Total Sessions</h2>
                        <div class="bg-white/20 p-2 rounded-lg">
                            <i class="fas fa-laptop-code text-xl"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold mt-2"><?php echo number_format($totalSessions); ?></p>
                    <p class="text-indigo-200 text-sm mt-1">Laboratory sessions attended</p>
                </div>
                
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 text-white p-6 rounded-xl shadow-md hover:shadow-xl transform hover:scale-105 transition duration-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-emerald-100">Labs Visited</h2>
                        <div class="bg-white/20 p-2 rounded-lg">
                            <i class="fas fa-building text-xl"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold mt-2"><?php echo number_format($totalLabs); ?></p>
                    <p class="text-emerald-200 text-sm mt-1">Unique laboratories visited</p>
                </div>
                
                <div class="bg-gradient-to-br from-amber-500 to-amber-600 text-white p-6 rounded-xl shadow-md hover:shadow-xl transform hover:scale-105 transition duration-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-amber-100">Feedback Given</h2>
                        <div class="bg-white/20 p-2 rounded-lg">
                            <i class="fas fa-comments text-xl"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold mt-2"><?php echo number_format($totalFeedback); ?></p>
                    <p class="text-amber-200 text-sm mt-1">Total feedback submitted</p>
                </div>
            </div>

            <!-- History Table Section -->
            <div class="bg-white rounded-xl shadow-md border border-gray-100">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="bg-indigo-100 p-2 rounded-lg">
                                <i class="fas fa-table text-indigo-600"></i>
                            </div>
                            <h2 class="text-xl font-bold text-gray-800">Session History</h2>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    <?php if ($sitInResult->num_rows > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="bg-gray-50 text-left">
                                        <th class="px-6 py-3 text-gray-600 font-semibold tracking-wider">
                                            <i class="fas fa-laptop-code mr-2"></i>Lab
                                        </th>
                                        <th class="px-6 py-3 text-gray-600 font-semibold tracking-wider">
                                            <i class="fas fa-clipboard-list mr-2"></i>Reason
                                        </th>
                                        <th class="px-6 py-3 text-gray-600 font-semibold tracking-wider">
                                            <i class="fas fa-clock mr-2"></i>In Time
                                        </th>
                                        <th class="px-6 py-3 text-gray-600 font-semibold tracking-wider">
                                            <i class="fas fa-clock mr-2"></i>Out Time
                                        </th>
                                        <th class="px-6 py-3 text-gray-600 font-semibold tracking-wider">
                                            <i class="fas fa-cogs mr-2"></i>Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php while ($row = $sitInResult->fetch_assoc()): ?>
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4"><?php echo htmlspecialchars($row['lab']); ?></td>
                                            <td class="px-6 py-4"><?php echo htmlspecialchars($row['reason']); ?></td>
                                            <td class="px-6 py-4"><?php echo htmlspecialchars($row['in_time']); ?></td>
                                            <td class="px-6 py-4"><?php echo htmlspecialchars($row['out_time']); ?></td>
                                            <td class="px-6 py-4">
                                                <button 
                                                    onclick="openModalWithLab('<?php echo htmlspecialchars($row['lab']); ?>')" 
                                                    class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all duration-200">
                                                    <i class="fas fa-flag mr-1"></i>
                                                    Report Issue
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <div class="bg-gray-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-history text-2xl text-gray-400"></i>
                            </div>
                            <h3 class="text-xl font-medium text-gray-600 mb-2">No Session History</h3>
                            <p class="text-gray-500">You haven't attended any laboratory sessions yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Feedback Modal -->
<div id="feedbackModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center backdrop-blur-sm z-50">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md mx-4 transform transition-all">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="bg-indigo-100 p-2 rounded-lg">
                        <i class="fas fa-flag text-indigo-600"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">Submit Feedback</h2>
                </div>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form action="submit_feedback.php" method="POST" id="feedbackForm">
                <input type="hidden" name="idno" value="<?php echo htmlspecialchars($idno); ?>">

                <div class="space-y-6">
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            <i class="fas fa-laptop-code mr-2"></i>Lab
                        </label>
                        <input type="text" name="lab" 
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" 
                               required readonly>
                    </div>

                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            <i class="fas fa-comment-alt mr-2"></i>Message
                        </label>
                        <textarea name="message" 
                                  class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" 
                                  rows="4" 
                                  required
                                  placeholder="Describe the issue you encountered..."></textarea>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal()" 
                            class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-all">
                        Cancel
                    </button>
                    <button type="button" id="feedbackSubmitBtn" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all">
                        <i class="fas fa-paper-plane mr-2"></i>Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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

/* Smooth transitions */
.transition-all {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Table styles */
table {
    border-collapse: separate;
    border-spacing: 0;
}

th, td {
    white-space: nowrap;
}

/* Modal animation */
.backdrop-blur-sm {
    backdrop-filter: blur(8px);
}

.transform {
    transform-origin: center;
}

.transition-all {
    transition: all 0.3s ease-in-out;
}
</style>

<script>
document.getElementById('feedbackSubmitBtn').addEventListener('click', function() {
    var lab = document.querySelector('#feedbackForm input[name="lab"]').value.trim();
    var message = document.querySelector('#feedbackForm textarea[name="message"]').value.trim();
    
    if (lab === "" || message === "") {
        Notiflix.Notify.failure("Please fill out all required fields.");
        return;
    }
    
    Notiflix.Confirm.show(
        'Confirm Submission',
        'Are you sure you want to submit this feedback?',
        'Yes',
        'No',
        function() {
            var formData = new FormData(document.getElementById('feedbackForm'));
            fetch('submit_feedback.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Notiflix.Report.success(
                        'Success',
                        data.message,
                        'OK',
                        () => {
                            closeModal();
                            document.getElementById('feedbackForm').reset();
                        }
                    );
                } else {
                    Notiflix.Report.failure('Error', data.message || 'Submission failed', 'OK');
                }
            })
            .catch(error => {
                Notiflix.Report.failure('Error', 'An error occurred: ' + error.message, 'OK');
            });
        }
    );
});

function openModalWithLab(lab) {
    document.querySelector('#feedbackForm input[name="lab"]').value = lab;
    document.getElementById("feedbackModal").classList.remove("hidden");
    document.body.style.overflow = 'hidden';
}

function openModal() {
    document.getElementById("feedbackModal").classList.remove("hidden");
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById("feedbackModal").classList.add("hidden");
    document.body.style.overflow = 'auto';
}
</script>

<?php require_once '../shared/footer.php'; ?>
