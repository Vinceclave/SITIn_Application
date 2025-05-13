<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/config.php';
require_once '../shared/header.php';
?>

<div class="flex min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 text-gray-900 pb-14">
    <?php include '../shared/aside.php'; ?>
    <main class="flex-1 p-6 pt-24">
        <div class="max-w-7xl mx-auto">
            <!-- Header Section -->
            <div class="bg-white bg-opacity-80 backdrop-blur-sm rounded-xl shadow-md p-6 mb-8 border border-gray-100">
                <div class="flex items-center space-x-4">
                    <div class="bg-blue-100 p-3 rounded-full">
                        <i class="fas fa-comment-dots text-2xl text-blue-600"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-800">Feedback Records</h1>
                        <p class="text-lg text-gray-600">View and manage student feedback</p>
                    </div>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100 mb-6">
                <h2 class="text-lg font-semibold text-gray-700 mb-4 flex items-center">
                    <i class="fas fa-search text-indigo-500 mr-2"></i>
                    Search & Filter
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-laptop text-gray-400"></i>
                        </div>
                        <input type="text" 
                               id="searchLab" 
                               placeholder="Search by Lab"
                               class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-id-card text-gray-400"></i>
                        </div>
                        <input type="text" 
                               id="searchIdno" 
                               placeholder="Search by Student ID"
                               class="w-full pl-10 pr-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                    <div class="flex items-center">
                        <button id="clearFilters" 
                                class="flex items-center justify-center w-full px-4 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors shadow-sm">
                            <i class="fas fa-eraser mr-2"></i>Clear Filters
                        </button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-xl font-bold text-gray-800 flex items-center">
                        <i class="fas fa-list text-indigo-500 mr-2"></i>
                        Feedback List
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Feedback ID</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student ID</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lab</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                            </tr>
                        </thead>
                        <tbody id="feedbackTable" class="divide-y divide-gray-200">
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    <i class="fas fa-circle-notch fa-spin mr-2"></i>
                                    Loading feedback data...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination - Upgraded design -->
                <div class="p-5 border-t border-gray-100">
                    <div id="pagination" class="flex justify-center space-x-2"></div>
                </div>
            </div>
            
            <!-- Summary Card -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl shadow-md p-6 text-white">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-semibold">Feedback Overview</h3>
                        <div class="bg-white/20 p-2 rounded-lg">
                            <i class="fas fa-chart-simple text-xl"></i>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div class="bg-white/10 p-4 rounded-lg">
                            <p class="text-indigo-100 text-sm">Total Feedback</p>
                            <h4 id="totalFeedback" class="text-2xl font-bold mt-1">--</h4>
                        </div>
                        <div class="bg-white/10 p-4 rounded-lg">
                            <p class="text-indigo-100 text-sm">Recent (30 days)</p>
                            <h4 id="recentFeedback" class="text-2xl font-bold mt-1">--</h4>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-xl font-semibold text-gray-800">Most Active Labs</h3>
                        <div class="bg-blue-100 p-2 rounded-lg">
                            <i class="fas fa-building text-blue-600"></i>
                        </div>
                    </div>
                    <div id="topLabs" class="space-y-2">
                        <!-- Will be populated via JavaScript -->
                        <div class="flex items-center">
                            <div class="w-full bg-gray-100 rounded-full h-4 mr-2">
                                <div class="bg-blue-500 h-4 rounded-full animate-pulse" style="width: 70%"></div>
                            </div>
                            <span class="text-sm text-gray-500 w-16 text-right">--</span>
                        </div>
                    </div>
                </div>
            </div>
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
        let limit = 10;

        // Show loading indicator
        document.getElementById("feedbackTable").innerHTML = `
            <tr>
                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                    <i class="fas fa-circle-notch fa-spin mr-2"></i>
                    Loading feedback data...
                </td>
            </tr>
        `;

        // Set HTTP headers for AJAX request
        const headers = new Headers();
        headers.append('X-Requested-With', 'XMLHttpRequest');

        fetch(`fetch_feedback.php?page=${page}&limit=${limit}&lab=${encodeURIComponent(lab)}&idno=${encodeURIComponent(idno)}`, {
            headers: headers
        })
            .then(response => response.json())
            .then(data => {
                // Update the table content
                document.getElementById("feedbackTable").innerHTML = data.html;
                
                // Update pagination
                updatePagination(page, data.pagination.total_pages);
                
                // Load summary data
                loadFeedbackSummary();
            })
            .catch(error => {
                console.error("Error fetching data:", error);
                document.getElementById("feedbackTable").innerHTML = `
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-red-500">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Error loading data. Please try again.
                        </td>
                    </tr>
                `;
            });
    }
    
    // Function to update pagination
    function updatePagination(currentPage, totalPages) {
        let paginationHTML = '';
        
        // Previous button
        paginationHTML += `
            <button class="px-3 py-1 rounded-md ${currentPage === 1 ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'}" 
                    ${currentPage === 1 ? 'disabled' : `onclick="fetchFeedback(${currentPage - 1})"`}>
                <i class="fas fa-chevron-left"></i>
            </button>
        `;
        
        // Page numbers
        // Logic to limit the number of page buttons shown
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        
        if (endPage - startPage < 4 && startPage > 1) {
            startPage = Math.max(1, endPage - 4);
        }
        
        // First page link if not in range
        if (startPage > 1) {
            paginationHTML += `
                <button class="px-3 py-1 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50"
                        onclick="fetchFeedback(1)">
                    1
                </button>
            `;
            if (startPage > 2) {
                paginationHTML += `
                    <span class="px-3 py-1 text-gray-500">...</span>
                `;
            }
        }
        
        // Page buttons
        for (let i = startPage; i <= endPage; i++) {
            paginationHTML += `
                <button class="px-3 py-1 rounded-md ${currentPage === i ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'}"
                        onclick="fetchFeedback(${i})">
                    ${i}
                </button>
            `;
        }
        
        // Last page link if not in range
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                paginationHTML += `
                    <span class="px-3 py-1 text-gray-500">...</span>
                `;
            }
            paginationHTML += `
                <button class="px-3 py-1 rounded-md bg-white border border-gray-300 text-gray-700 hover:bg-gray-50"
                        onclick="fetchFeedback(${totalPages})">
                    ${totalPages}
                </button>
            `;
        }
        
        // Next button
        paginationHTML += `
            <button class="px-3 py-1 rounded-md ${currentPage === totalPages ? 'bg-gray-200 text-gray-400 cursor-not-allowed' : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'}" 
                    ${currentPage === totalPages ? 'disabled' : `onclick="fetchFeedback(${currentPage + 1})"`}>
                <i class="fas fa-chevron-right"></i>
            </button>
        `;
        
        document.getElementById("pagination").innerHTML = paginationHTML;
    }
    
    // Function to load feedback summary data from the backend
    function loadFeedbackSummary() {
        fetch('fetch_feedback_stats.php')
            .then(response => response.json())
            .then(data => {
                // Update summary numbers
                document.getElementById("totalFeedback").textContent = data.total.toLocaleString();
                document.getElementById("recentFeedback").textContent = data.recent.toLocaleString();
                
                // Update top labs visualization
                if (data.top_labs && data.top_labs.length > 0) {
                    // Find the maximum count for percentage calculation
                    const maxCount = Math.max(...data.top_labs.map(item => item.count));
                    let topLabsHTML = '';
                    
                    data.top_labs.forEach(lab => {
                        const percentage = (lab.count / maxCount) * 100;
                        topLabsHTML += `
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex-1 mr-4">
                                    <div class="flex justify-between mb-1">
                                        <span class="text-sm font-medium text-gray-700">${lab.lab}</span>
                                        <span class="text-sm text-gray-500">${lab.count}</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-2.5">
                                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: ${percentage}%"></div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    
                    document.getElementById("topLabs").innerHTML = topLabsHTML;
                } else {
                    document.getElementById("topLabs").innerHTML = `
                        <div class="text-center text-gray-500 py-4">
                            No lab data available
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error("Error fetching summary data:", error);
                // Display error in summary sections
                document.getElementById("totalFeedback").textContent = "--";
                document.getElementById("recentFeedback").textContent = "--";
                document.getElementById("topLabs").innerHTML = `
                    <div class="text-center text-red-500 py-2">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Error loading data
                    </div>
                `;
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

    // Make fetchFeedback available globally
    window.fetchFeedback = fetchFeedback;

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

<?php require_once '../shared/footer.php'; ?>
