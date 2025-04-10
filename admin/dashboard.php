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

<div class="flex min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 text-gray-900 pb-14">
    <?php include '../shared/aside.php'; ?>
    <main class="flex-1 p-6 ml-64">
        <div class="max-w-[1400px] mx-auto">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 mb-2">Admin Dashboard</h1>
                    <p class="text-lg text-gray-600">Manage and monitor your system's performance</p>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm bg-white px-4 py-2 rounded-lg shadow-sm border border-gray-200">
                        <i class="fas fa-clock mr-2 text-indigo-600"></i>
                        Last updated: <?php echo date('M d, Y h:i A'); ?>
                    </span>
                </div>
            </div>

            <!-- Totals Section -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100 transform hover:-translate-y-1">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-indigo-50 rounded-lg">
                            <i class="fas fa-users text-indigo-600 text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-medium text-gray-600">Total Students</h2>
                            <p class="text-3xl font-bold text-gray-800"><?php echo $totalStudents; ?></p>
                            <p class="text-sm text-gray-500 mt-1">Registered students in the system</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100 transform hover:-translate-y-1">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-green-50 rounded-lg">
                            <i class="fas fa-calendar-check text-green-600 text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-medium text-gray-600">Total Sessions</h2>
                            <p class="text-3xl font-bold text-gray-800"><?php echo $totalSessions; ?></p>
                            <p class="text-sm text-gray-500 mt-1">Completed sit-in sessions</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100 transform hover:-translate-y-1">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <i class="fas fa-bookmark text-blue-600 text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-medium text-gray-600">Total Reservations</h2>
                            <p class="text-3xl font-bold text-gray-800"><?php echo $totalReservations; ?></p>
                            <p class="text-sm text-gray-500 mt-1">Active lab reservations</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100 transform hover:-translate-y-1">
                    <div class="flex items-center space-x-4">
                        <div class="p-3 bg-purple-50 rounded-lg">
                            <i class="fas fa-building text-purple-600 text-2xl"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-medium text-gray-600">Total Labs</h2>
                            <p class="text-3xl font-bold text-gray-800"><?php echo $totalLabs; ?></p>
                            <p class="text-sm text-gray-500 mt-1">Available computer labs</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800">Sessions Overview</h2>
                            <p class="text-sm text-gray-500 mt-1">Monthly session distribution</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                    </div>
                    <div class="h-[300px]">
                        <canvas id="sessionChart" class="w-full h-full"></canvas>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800">Create Announcement</h2>
                            <p class="text-sm text-gray-500 mt-1">Post important updates for students</p>
                        </div>
                    </div>
                    <form method="POST" class="space-y-4">
                        <textarea name="message" required 
                                class="w-full p-4 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all resize-none" 
                                rows="4" 
                                placeholder="Type your announcement here..."></textarea>
                        <div class="flex justify-end">
                            <button type="submit" name="add_announcement" 
                                    class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all duration-300 transform hover:-translate-y-0.5">
                                <i class="fas fa-paper-plane mr-2"></i>Post Announcement
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Announcements Section -->
            <div class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800">Recent Announcements</h2>
                        <p class="text-sm text-gray-500 mt-1">Latest updates and notifications</p>
                    </div>
                    <button class="p-2 text-gray-400 hover:text-gray-600 transition-colors" onclick="loadAnnouncements()">
                        <i class="fas fa-sync-alt"></i>
                    </button>
                </div>
                <ul id="announcementList" class="space-y-4"></ul>
                <div id="loadingMessage" class="text-center text-gray-500 py-4 hidden">
                    <div class="flex items-center justify-center space-x-2">
                        <i class="fas fa-spinner fa-spin"></i>
                        <span>Loading announcements...</span>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once '../shared/footer.php'; ?>

<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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
// Static chart data (replace these with real static data)
const chartData = {
    labels: ['Session 1', 'Session 2', 'Session 3', 'Session 4'],
    sessions: [120, 250, 300, 500]
};

document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('sessionChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Sessions Used',
                data: chartData.sessions,
                backgroundColor: 'rgba(99, 102, 241, 0.6)',
                borderColor: 'rgba(99, 102, 241, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
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

function loadAnnouncements() {
    if (loading || allLoaded) return;
    loading = true;
    document.getElementById("loadingMessage").classList.remove("hidden");

    fetch(`fetch_announcements.php?offset=${offset}&limit=${limit}`)
        .then(response => response.text())
        .then(data => {
            data = data.trim();
            if (!data) {
                allLoaded = true;
            } else {
                document.getElementById("announcementList").insertAdjacentHTML("beforeend", data);
                offset += limit;
            }

            loading = false;
            document.getElementById("loadingMessage").classList.add("hidden");
        })
        .catch(error => {
            console.error("Error loading announcements:", error);
            loading = false;
            document.getElementById("loadingMessage").classList.add("hidden");
        });
}

document.addEventListener("DOMContentLoaded", function () {
    loadAnnouncements();

    window.addEventListener("scroll", function () {
        if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 200) {
            loadAnnouncements();
        }
    });
});
</script>
