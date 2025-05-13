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

// Ensure role is set
$role = isset($user['role']) ? $user['role'] : 'Student';

// Check if user is admin
if (strcasecmp($role, 'Admin') !== 0) {
    header("Location: home.php");
    exit;
}

// Static values for totals (replace these with real static values)
$totalStudents = 1500;
$totalSessions = 12000;
$totalReservations = 320;
$totalLabs = 15;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_announcement'])) {
    $message = trim($_POST['message']);
    if (empty($message)) {
        $_SESSION['error'] = "Announcement message cannot be empty.";
    } else {
        // Updated query using admin_name and date instead of created_at
        $stmt = $conn->prepare("INSERT INTO announcements (admin_name, date, message) VALUES (?, NOW(), ?)");
        if ($stmt) {
            $admin_name = $user['username'];
            $stmt->bind_param("ss", $admin_name, $message);
            if ($stmt->execute()) {
                $_SESSION['success'] = "Announcement posted successfully.";
            } else {
                $_SESSION['error'] = "Failed to post announcement: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $_SESSION['error'] = "Database error: " . $conn->error;
        }
    }
    header("Location: dashboard.php");
    exit;
}
?>

<?php
if(isset($_SESSION['success'])):
?>
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
                        <i class="fas fa-user-shield text-2xl text-indigo-600"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Admin Dashboard</h1>
                        <p class="text-lg text-gray-600">Welcome, <span class="font-medium text-indigo-600"><?php echo htmlspecialchars($user['username']); ?></span>!</p>
                    </div>
                </div>
            </div>

            <!-- Totals Section -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 text-white p-6 rounded-xl shadow-md hover:shadow-xl transform hover:scale-105 transition duration-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-indigo-100">Total Students</h2>
                        <div class="bg-white/20 p-2 rounded-lg">
                            <i class="fas fa-user-graduate text-xl"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold mt-2"><?php echo number_format($totalStudents); ?></p>
                    <p class="text-indigo-200 text-sm mt-1">Active students in the system</p>
                </div>
                
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 text-white p-6 rounded-xl shadow-md hover:shadow-xl transform hover:scale-105 transition duration-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-emerald-100">Total Sessions</h2>
                        <div class="bg-white/20 p-2 rounded-lg">
                            <i class="fas fa-laptop-code text-xl"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold mt-2"><?php echo number_format($totalSessions); ?></p>
                    <p class="text-emerald-200 text-sm mt-1">Sessions conducted to date</p>
                </div>
                
                <div class="bg-gradient-to-br from-amber-500 to-amber-600 text-white p-6 rounded-xl shadow-md hover:shadow-xl transform hover:scale-105 transition duration-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-amber-100">Total Reservations</h2>
                        <div class="bg-white/20 p-2 rounded-lg">
                            <i class="fas fa-calendar-check text-xl"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold mt-2"><?php echo number_format($totalReservations); ?></p>
                    <p class="text-amber-200 text-sm mt-1">Lab reservations made</p>
                </div>
                
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 rounded-xl shadow-md hover:shadow-xl transform hover:scale-105 transition duration-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold text-blue-100">Total Labs</h2>
                        <div class="bg-white/20 p-2 rounded-lg">
                            <i class="fas fa-building text-xl"></i>
                        </div>
                    </div>
                    <p class="text-3xl font-bold mt-2"><?php echo number_format($totalLabs); ?></p>
                    <p class="text-blue-200 text-sm mt-1">Active computer labs</p>
                </div>
            </div>

            <!-- Chart Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <div class="bg-white p-6 rounded-xl shadow-md lg:col-span-2 border border-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-chart-bar text-indigo-600 mr-2"></i>
                            Sessions Overview
                        </h2>
                        <div class="text-sm text-gray-500">Last 7 days</div>
                    </div>
                    <div class="h-80">
                        <canvas id="sessionChart"></canvas>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                        <i class="fas fa-bolt text-amber-500 mr-2"></i>
                        Quick Actions
                    </h2>
                    <div class="space-y-4">
                        <a href="manage_users.php" class="flex items-center p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                            <div class="bg-indigo-100 p-2 rounded-lg mr-4">
                                <i class="fas fa-users text-indigo-600"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-800">Manage Users</h3>
                                <p class="text-sm text-gray-500">Add, edit or remove users</p>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
                        </a>
                        
                        <a href="manage_reservations.php" class="flex items-center p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                            <div class="bg-emerald-100 p-2 rounded-lg mr-4">
                                <i class="fas fa-calendar-alt text-emerald-600"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-800">Reservations</h3>
                                <p class="text-sm text-gray-500">View and approve reservations</p>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
                        </a>
                        
                        <a href="reports.php" class="flex items-center p-4 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors">
                            <div class="bg-blue-100 p-2 rounded-lg mr-4">
                                <i class="fas fa-chart-line text-blue-600"></i>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-800">Reports</h3>
                                <p class="text-sm text-gray-500">View detailed analytics</p>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Announcements Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                <div class="bg-white p-6 rounded-xl shadow-md lg:col-span-1 border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-bullhorn text-red-500 mr-2"></i>
                        Create Announcement
                    </h2>
                    <form method="POST" class="space-y-4">
                        <textarea name="message" required class="w-full p-4 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow" 
                            rows="5" placeholder="Type your announcement here..."></textarea>
                        <button type="submit" name="add_announcement" 
                            class="w-full px-5 py-3 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition-colors shadow-sm flex items-center justify-center">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Post Announcement
                        </button>
                    </form>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-md lg:col-span-2 border border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-bell text-amber-500 mr-2"></i>
                        Recent Announcements
                    </h2>
                    <div class="overflow-y-auto max-h-[350px] pr-2">
                        <ul id="announcementList" class="space-y-4">
                            <!-- Announcements will be loaded here -->
                        </ul>
                    </div>
                    <div id="loadingMessage" class="text-center py-4 text-gray-500 hidden">
                        <i class="fas fa-circle-notch fa-spin mr-2"></i>
                        Loading more...
                    </div>
                </div>
            </div>
            
            <!-- Recent Feedback Section -->
            <div class="bg-white p-6 rounded-xl shadow-md mb-8 border border-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-comment-alt text-blue-500 mr-2"></i>
                        Recent Student Feedback
                    </h2>
                    <a href="admin_feedback.php" class="text-sm text-indigo-600 hover:text-indigo-800 flex items-center">
                        <span>View All</span>
                        <i class="fas fa-chevron-right ml-1 text-xs"></i>
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lab</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                            </tr>
                        </thead>
                        <tbody id="recentFeedbackTable" class="divide-y divide-gray-200">
                            <tr>
                                <td colspan="4" class="px-4 py-3 text-center text-gray-500">
                                    <i class="fas fa-circle-notch fa-spin mr-2"></i>
                                    Loading feedback...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once '../shared/footer.php'; ?>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    navy: "#24292e",
                    darkblue: "#0366d6",
                    steelblue: "#f6f8fa",
                    bluegray: "#6a737d"
                }
            }
        }
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart data 
const chartData = {
    labels: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
    sessions: [120, 250, 300, 180, 250, 70, 40]
};

document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('sessionChart').getContext('2d');
    
    // Create gradient
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(79, 70, 229, 0.6)');
    gradient.addColorStop(1, 'rgba(79, 70, 229, 0.1)');
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Sessions Used',
                data: chartData.sessions,
                backgroundColor: gradient,
                borderColor: 'rgba(79, 70, 229, 1)',
                borderWidth: 2,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: 'rgba(79, 70, 229, 1)',
                pointBorderWidth: 2,
                pointRadius: 4,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { 
                    beginAtZero: true,
                    grid: {
                        drawBorder: false,
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                    titleColor: '#1e293b',
                    bodyColor: '#1e293b',
                    borderColor: 'rgba(0, 0, 0, 0.1)',
                    borderWidth: 1,
                    padding: 10,
                    boxPadding: 5,
                    usePointStyle: true,
                    callbacks: {
                        title: function(tooltipItem) {
                            return tooltipItem[0].label;
                        },
                        label: function(context) {
                            return `Sessions: ${context.raw}`;
                        }
                    }
                }
            }
        }
    });
});

// Infinite Scroll for Announcements
let offset = 0;
const limit = 5;
let loading = false;
let allLoaded = false;

function formatAnnouncement(announcement) {
    const date = new Date(announcement.date);
    const formattedDate = date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    return `
    <li class="p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
        <div class="flex items-start">
            <div class="bg-indigo-100 p-2 rounded-full mr-3">
                <i class="fas fa-bullhorn text-indigo-600"></i>
            </div>
            <div class="flex-1">
                <p class="text-gray-800">${announcement.message}</p>
                <div class="flex items-center justify-between mt-2 text-sm">
                    <span class="text-gray-500">
                        <i class="fas fa-user-edit mr-1"></i>
                        ${announcement.admin_name}
                    </span>
                    <span class="text-gray-500">
                        <i class="far fa-clock mr-1"></i>
                        ${formattedDate}
                    </span>
                </div>
            </div>
        </div>
    </li>
    `;
}

function loadAnnouncements() {
    if (loading || allLoaded) return;
    loading = true;
    document.getElementById("loadingMessage").classList.remove("hidden");

    fetch(`fetch_announcements.php?offset=${offset}&limit=${limit}`)
        .then(response => response.json())
        .then(data => {
            if (!data || data.length === 0) {
                allLoaded = true;
                if (offset === 0) {
                    document.getElementById("announcementList").innerHTML = `
                        <li class="text-center p-6 text-gray-500">
                            <i class="fas fa-info-circle text-xl mb-2"></i>
                            <p>No announcements found</p>
                        </li>
                    `;
                }
            } else {
                const html = data.map(announcement => formatAnnouncement(announcement)).join('');
                document.getElementById("announcementList").insertAdjacentHTML("beforeend", html);
                offset += limit;
            }

            loading = false;
            document.getElementById("loadingMessage").classList.add("hidden");
        })
        .catch(error => {
            console.error("Error loading announcements:", error);
            loading = false;
            document.getElementById("loadingMessage").classList.add("hidden");
            document.getElementById("announcementList").innerHTML += `
                <li class="text-center p-4 text-red-500">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Error loading announcements
                </li>
            `;
        });
}

// Load recent feedback
function loadRecentFeedback() {
    fetch('get_recent_feedback.php?limit=5')
        .then(response => response.text())
        .then(data => {
            document.getElementById("recentFeedbackTable").innerHTML = data;
        })
        .catch(error => {
            console.error("Error loading feedback:", error);
            document.getElementById("recentFeedbackTable").innerHTML = `
                <tr>
                    <td colspan="4" class="px-4 py-3 text-center text-red-500">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Error loading feedback
                    </td>
                </tr>
            `;
        });
}

document.addEventListener("DOMContentLoaded", function () {
    // Load announcements
    loadAnnouncements();

    // Load recent feedback
    loadRecentFeedback();

    // Detect when user scrolls to bottom of announcements container
    const announcementContainer = document.querySelector('.overflow-y-auto');
    announcementContainer.addEventListener("scroll", function() {
        if (announcementContainer.scrollTop + announcementContainer.clientHeight >= announcementContainer.scrollHeight - 50) {
            loadAnnouncements();
        }
    });
});
</script>
