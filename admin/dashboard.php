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
?>

<div class="flex min-h-screen bg-white text-gray-900 pb-14">
    <?php include '../shared/aside.php'; ?>
    <main class="flex-1 p-8 ml-60">
        <h1 class="text-2xl font-medium mb-4">Admin Dashboard</h1>
        <p class="text-gray-600 mb-6">Welcome, <?php echo htmlspecialchars($user['username']); ?>!</p>

        <!-- Totals Section -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold text-gray-700">📚 Total Students</h2>
                <p class="text-2xl font-bold text-navy"><?php echo $totalStudents; ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold text-gray-700">📅 Total Sessions</h2>
                <p class="text-2xl font-bold text-navy"><?php echo $totalSessions; ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold text-gray-700">🛎️ Total Reservations</h2>
                <p class="text-2xl font-bold text-navy"><?php echo $totalReservations; ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow">
                <h2 class="text-lg font-semibold text-gray-700">🏢 Total Labs</h2>
                <p class="text-2xl font-bold text-navy"><?php echo $totalLabs; ?></p>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">
            <h2 class="text-xl font-medium mb-4">📊 Sessions Overview</h2>
            <canvas id="sessionChart"></canvas>
        </div>

        <!-- Announcements Section -->
        <div class="bg-white p-6 rounded-lg shadow mb-6">
            <h2 class="text-xl font-medium mb-4">Create Announcement</h2>
            <form method="POST">
                <textarea 
                    name="message" 
                    required
                    class="w-full p-2 border border-gray-300 rounded-md bg-white text-gray-900 focus:ring-2 focus:ring-gray-400"
                    rows="3"
                    placeholder="Enter your announcement..."></textarea>
                <button type="submit" name="add_announcement" class="bg-gray-700 text-white px-4 py-2 rounded-md mt-2 hover:bg-gray-900">
                    Post
                </button>
            </form>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-medium mb-4">Announcements</h2>
            <ul id="announcementList" class="space-y-3"></ul>
            <p id="loadingMessage" class="text-center text-gray-500 hidden">Loading...</p>
        </div>
    </main>
</div>

<?php require_once '../shared/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Static chart data (replace these with real static data)
const chartData = {
    labels: ['Session 1', 'Session 2', 'Session 3', 'Session 4'], // Replace with appropriate labels
    sessions: [120, 250, 300, 500] // Replace with actual session data
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
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
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
