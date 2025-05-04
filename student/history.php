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
<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.5/dist/notiflix-aio-3.2.5.min.js"></script>

<div class="container max-w-[1400px] mx-auto mt-20 p-6 flex">
    <?php include '../shared/aside.php'; ?>
    <main class="w-full p-4 sm:p-6 md:p-8 lg:p-10">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl p-6 sm:p-8 text-white mb-8 shadow-lg">
            <div class="flex items-center gap-4">
                <i class="fas fa-history text-4xl opacity-90"></i>
                <div>
                    <h1 class="text-3xl sm:text-4xl font-bold">Lab Session History</h1>
                    <p class="text-indigo-100 mt-2">Track your laboratory usage and sessions</p>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-4 p-4 bg-green-100 border border-green-200 text-green-700 rounded-lg flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="mb-4 p-4 bg-red-100 border border-red-200 text-red-700 rounded-lg flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <!-- History Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200/50 backdrop-blur-sm overflow-hidden">
            <div class="border-b border-gray-200/50 px-6 py-4 flex items-center gap-3">
                <i class="fas fa-table text-indigo-600"></i>
                <h2 class="text-xl font-semibold">Session History</h2>
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
                                <?php 
                                $sitInStmt->execute();
                                $sitInResult = $sitInStmt->get_result();
                                while ($row = $sitInResult->fetch_assoc()): 
                                ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($row['lab']); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($row['reason']); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($row['in_time']); ?></td>
                                        <td class="px-6 py-4"><?php echo htmlspecialchars($row['out_time']); ?></td>
                                        <td class="px-6 py-4">
                                            <button 
                                                onclick="openModalWithLab('<?php echo htmlspecialchars($row['lab']); ?>')" 
                                                class="inline-flex items-center px-3 py-1.5 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition-all duration-200 shadow-sm hover:shadow-md">
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
                    <div class="text-center py-8">
                        <i class="fas fa-inbox text-4xl text-gray-400 mb-3"></i>
                        <p class="text-gray-600">No session history found.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- Feedback Modal -->
<div id="feedbackModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md mx-4 transform transition-all">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-semibold flex items-center">
                    <i class="fas fa-flag text-indigo-600 mr-2"></i>
                    Submit Feedback
                </h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form action="submit_feedback.php" method="POST" id="feedbackForm">
                <input type="hidden" name="idno" value="<?php echo htmlspecialchars($idno); ?>">

                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">
                        <i class="fas fa-laptop-code mr-2"></i>Lab
                    </label>
                    <input type="text" name="lab" 
                           class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" 
                           required>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-medium mb-2">
                        <i class="fas fa-comment-alt mr-2"></i>Message
                    </label>
                    <textarea name="message" 
                              class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" 
                              rows="4" 
                              required></textarea>
                </div>

                <div class="flex justify-end space-x-3">
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
