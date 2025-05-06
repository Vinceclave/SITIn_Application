<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/config.php';
require_once '../shared/header.php';
?>

<div class="mt-10 flex min-h-screen bg-gray-50 text-gray-900">
    <?php include '../shared/aside.php'; ?>
    <main class="flex-1 pt-10 p-6">
        <div class="max-w-[1400px] mx-auto">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-semibold text-gray-800">Feedback Records</h1>
                    <p class="text-lg text-gray-600">View and manage student feedback</p>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-laptop text-gray-400"></i>
                        </div>
                        <input type="text" 
                               id="searchLab" 
                               placeholder="Search by Lab"
                               class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-id-card text-gray-400"></i>
                        </div>
                        <input type="text" 
                               id="searchIdno" 
                               placeholder="Search by Student ID"
                               class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <button id="clearFilters" 
                                class="flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                            <i class="fas fa-eraser mr-2"></i>Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Feedback ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lab</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                            </tr>
                        </thead>
                        <tbody id="feedbackTable" class="divide-y divide-gray-200"></tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div id="pagination" class="flex justify-center mt-6 space-x-2"></div>
        </div>
    </main>
</div>

<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<script>
document.addEventListener("DOMContentLoaded", function () {
    // Function to fetch feedback with debounce
    let searchTimeout;
    function debounceSearch(func, wait) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(func, wait);
    }

    function fetchFeedback(page = 1) {
        let lab = document.getElementById("searchLab").value;
        let idno = document.getElementById("searchIdno").value;

        fetch(`fetch_feedback.php?page=${page}&lab=${encodeURIComponent(lab)}&idno=${encodeURIComponent(idno)}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById("feedbackTable").innerHTML = data;
            })
            .catch(error => {
                console.error("Error fetching data:", error);
                // You can add Notiflix notification here if you want
            });

    }

    // Add debounce to search inputs
    document.getElementById("searchLab").addEventListener("input", function() {
        debounceSearch(fetchFeedback, 300);
    });
    document.getElementById("searchIdno").addEventListener("input", function() {
        debounceSearch(fetchFeedback, 300);
    });

    document.getElementById("clearFilters").addEventListener("click", function () {
        document.getElementById("searchLab").value = "";
        document.getElementById("searchIdno").value = "";
        fetchFeedback();
    });

    // Initial fetch
    fetchFeedback();
});
</script>

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

<?php require_once '../shared/footer.php'; ?><?php require_once '../shared/footer.php'; ?>
