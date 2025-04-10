<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/config.php';
require_once '../shared/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<div class="flex min-h-screen bg-gray-50 text-gray-900">
    <?php include '../shared/aside.php'; ?>
    <main class="flex-1 p-4 ml-64">
        <div class="max-w-[1400px] mx-auto">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-semibold text-gray-800">Sit-in Records</h1>
                    <p class="text-lg text-gray-600">Manage and monitor student sit-in sessions</p>
                </div>
            </div>

            <!-- Search & Filters -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input type="text" 
                               id="searchName" 
                               placeholder="Search by Student Name"
                               class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-laptop text-gray-400"></i>
                        </div>
                        <input type="text" 
                               id="searchLab" 
                               placeholder="Search by Lab"
                               class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                    <div class="flex space-x-3">
                        <button id="clearFilters" 
                                class="flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                            <i class="fas fa-eraser mr-2"></i>Clear
                        </button>
                        <button id="resetSessions" 
                                class="flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                            <i class="fas fa-redo-alt mr-2"></i>Reset Sessions
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-calendar-alt text-gray-400"></i>
                        </div>
                        <input type="date" 
                               id="startDate" 
                               placeholder="Start Date"
                               class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-calendar-alt text-gray-400"></i>
                        </div>
                        <input type="date" 
                               id="endDate" 
                               placeholder="End Date"
                               class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                    <div class="flex space-x-3">
                        <button id="applyDateFilter" 
                                class="flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-filter mr-2"></i>Apply Date Filter
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lab</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status & Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Session & Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody id="sitInTable" class="divide-y divide-gray-200"></tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div id="pagination" class="flex justify-between items-center mt-6 bg-white p-4 rounded-xl shadow-sm border border-gray-100"></div>
        </div>
    </main>
</div>

<!-- Insert Notiflix library -->
<script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.5/dist/notiflix-aio-3.2.5.min.js"></script>
<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Configure Notiflix
        Notiflix.Notify.init({
            position: 'right-top',
            cssAnimation: true,
            cssAnimationDuration: 400,
            cssAnimationStyle: 'fade'
        });

        // Function to fetch sit-in records data
        function fetchData(page = 1) {
            let name = document.getElementById("searchName").value;
            let lab = document.getElementById("searchLab").value;
            let startDate = document.getElementById("startDate").value;
            let endDate = document.getElementById("endDate").value;

            fetch(`sit_in_fetch.php?page=${page}&name=${encodeURIComponent(name)}&lab=${encodeURIComponent(lab)}&startDate=${encodeURIComponent(startDate)}&endDate=${encodeURIComponent(endDate)}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById("sitInTable").innerHTML = data.tableHtml;
                    createPagination(data.pagination);
                })
                .catch(error => {
                    console.error("Error fetching data:", error);
                    Notiflix.Notify.failure("Error fetching data.");
                });
        }

        // Function to create pagination controls
        function createPagination(pagination) {
            const paginationElement = document.getElementById('pagination');
            paginationElement.innerHTML = '';
            
            if (pagination.totalPages <= 1) {
                return; // No pagination needed
            }
            
            // Create info text
            const infoDiv = document.createElement('div');
            infoDiv.className = 'text-sm text-gray-500 mr-4 flex items-center';
            infoDiv.textContent = `Page ${pagination.currentPage} of ${pagination.totalPages} (${pagination.totalRecords} records)`;
            paginationElement.appendChild(infoDiv);
            
            // Create a container for the pagination buttons
            const buttonsDiv = document.createElement('div');
            buttonsDiv.className = 'flex space-x-2';
            
            // Previous button
            if (pagination.currentPage > 1) {
                const prevButton = createPaginationButton('Previous', pagination.currentPage - 1);
                prevButton.innerHTML = '<i class="fas fa-chevron-left mr-1"></i> Prev';
                buttonsDiv.appendChild(prevButton);
            }
            
            // Page number buttons
            const maxVisiblePages = 5;
            let startPage = Math.max(1, pagination.currentPage - Math.floor(maxVisiblePages / 2));
            let endPage = Math.min(pagination.totalPages, startPage + maxVisiblePages - 1);
            
            // Adjust startPage if we are showing fewer than maxVisiblePages
            if (endPage - startPage + 1 < maxVisiblePages && startPage > 1) {
                startPage = Math.max(1, endPage - maxVisiblePages + 1);
            }
            
            // First page and ellipsis if needed
            if (startPage > 1) {
                buttonsDiv.appendChild(createPaginationButton('1', 1));
                if (startPage > 2) {
                    const ellipsis = document.createElement('span');
                    ellipsis.className = 'px-3 py-2 text-gray-500';
                    ellipsis.textContent = '...';
                    buttonsDiv.appendChild(ellipsis);
                }
            }
            
            // Page numbers
            for (let i = startPage; i <= endPage; i++) {
                const pageButton = createPaginationButton(i.toString(), i);
                if (i === pagination.currentPage) {
                    pageButton.classList.add('bg-indigo-600', 'text-white');
                    pageButton.classList.remove('bg-gray-200', 'hover:bg-gray-300');
                }
                buttonsDiv.appendChild(pageButton);
            }
            
            // Last page and ellipsis if needed
            if (endPage < pagination.totalPages) {
                if (endPage < pagination.totalPages - 1) {
                    const ellipsis = document.createElement('span');
                    ellipsis.className = 'px-3 py-2 text-gray-500';
                    ellipsis.textContent = '...';
                    buttonsDiv.appendChild(ellipsis);
                }
                buttonsDiv.appendChild(createPaginationButton(pagination.totalPages.toString(), pagination.totalPages));
            }
            
            // Next button
            if (pagination.currentPage < pagination.totalPages) {
                const nextButton = createPaginationButton('Next', pagination.currentPage + 1);
                nextButton.innerHTML = 'Next <i class="fas fa-chevron-right ml-1"></i>';
                buttonsDiv.appendChild(nextButton);
            }
            
            paginationElement.appendChild(buttonsDiv);
        }
        
        // Helper function to create pagination buttons
        function createPaginationButton(text, page) {
            const button = document.createElement('button');
            button.className = 'px-3 py-2 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors';
            button.textContent = text;
            button.addEventListener('click', function() {
                fetchData(page);
                
                // Scroll to top of the table
                document.querySelector('.overflow-x-auto').scrollIntoView({ behavior: 'smooth' });
            });
            return button;
        }

        // Handle the "End" button click event with validation and error trapping
        document.getElementById("sitInTable").addEventListener("click", function (event) {
            if (event.target && event.target.classList.contains("end-btn")) {
                let idno = event.target.getAttribute("data-id");
                if (!idno) {
                    Notiflix.Notify.failure("Invalid Student ID.");
                    return;
                }
                // Send a POST request to end the sit-in
                fetch("sit_in_end.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `idno=${idno}`
                })
                .then(response => response.text())
                .then(text => {
                    if (!text) {
                        throw new Error("Empty response received from server.");
                    }
                    try {
                        const jsonResponse = JSON.parse(text);
                        if (jsonResponse.success) {
                            Notiflix.Notify.success(jsonResponse.message);
                            fetchData(); // Refresh table after update
                        } else {
                            Notiflix.Notify.failure("Error: " + jsonResponse.message);
                        }
                    } catch (e) {
                        console.error("Error parsing JSON:", text);
                        throw e;
                    }
                })
                .catch(error => {
                    console.error("Fetch error:", error);
                    Notiflix.Notify.failure("There was an error processing the request.");
                });
            } else if (event.target && event.target.classList.contains("points-btn")) {
                let idno = event.target.getAttribute("data-id");
                let sitInId = event.target.getAttribute("data-sit-in-id");
                let pointsSelect = event.target.previousElementSibling;
                let points = pointsSelect ? pointsSelect.value : 1;
                
                if (!idno || !sitInId) {
                    Notiflix.Notify.failure("Invalid Student ID or Sit-in ID.");
                    return;
                }
                
                // Confirm before giving points
                Notiflix.Confirm.show(
                    'Give Points',
                    `Are you sure you want to award ${points} point(s) to this student?<br><br>Note: This will increment their session count by 1, regardless of how many points are given. Points cannot be given if the student has already reached 30 sessions.`,
                    'Yes',
                    'No',
                    function() {
                        // Send POST request to give points
                        fetch("give_points.php", {
                            method: "POST",
                            headers: { "Content-Type": "application/x-www-form-urlencoded" },
                            body: `idno=${idno}&sit_in_id=${sitInId}&points=${points}`
                        })
                        .then(response => response.text())
                        .then(text => {
                            if (!text) {
                                throw new Error("Empty response received from server.");
                            }
                            try {
                                const jsonResponse = JSON.parse(text);
                                if (jsonResponse.success) {
                                    Notiflix.Notify.success(jsonResponse.message);
                                    fetchData(); // Refresh table after update
                                } else {
                                    Notiflix.Notify.failure("Error: " + jsonResponse.message);
                                }
                            } catch (e) {
                                console.error("Error parsing JSON:", text);
                                throw e;
                            }
                        })
                        .catch(error => {
                            console.error("Fetch error:", error);
                            Notiflix.Notify.failure("There was an error processing the request.");
                        });
                    }
                );
            } else if (event.target && event.target.classList.contains("view-btn")) {
                let idno = event.target.getAttribute("data-id");
                if (!idno) {
                    Notiflix.Notify.failure("Invalid Student ID.");
                    return;
                }
                // Show session details in a modal
                Notiflix.Report.show(
                    'Session Details',
                    'Loading session details...',
                    'Close',
                    function() {
                        // Close callback
                    }
                );
                
                // Fetch session details
                fetch(`get_session_details.php?idno=${idno}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Notiflix.Report.show(
                                'Session Details',
                                `<div class="text-left">
                                    <p class="mb-2"><strong>Student ID:</strong> ${data.details.idno}</p>
                                    <p class="mb-2"><strong>Name:</strong> ${data.details.full_name}</p>
                                    <p class="mb-2"><strong>Lab:</strong> ${data.details.lab}</p>
                                    <p class="mb-2"><strong>Reason:</strong> ${data.details.reason}</p>
                                    <p class="mb-2"><strong>In Time:</strong> ${data.details.in_time}</p>
                                    <p class="mb-2"><strong>Out Time:</strong> ${data.details.out_time}</p>
                                    <p class="mb-2"><strong>Duration:</strong> ${data.details.duration}</p>
                                </div>`,
                                'Close'
                            );
                        } else {
                            Notiflix.Notify.failure("Error fetching session details.");
                        }
                    })
                    .catch(error => {
                        console.error("Error fetching session details:", error);
                        Notiflix.Notify.failure("Error fetching session details.");
                    });
            }
        });

        // Search and filter functionality with debounce
        let searchTimeout;
        function debounceSearch(func, wait) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(func, wait);
        }

        document.getElementById("searchName").addEventListener("input", function() {
            debounceSearch(fetchData, 300);
        });
        document.getElementById("searchLab").addEventListener("input", function() {
            debounceSearch(fetchData, 300);
        });

        // Clear search filters
        document.getElementById("clearFilters").addEventListener("click", function () {
            document.getElementById("searchName").value = "";
            document.getElementById("searchLab").value = "";
            document.getElementById("startDate").value = "";
            document.getElementById("endDate").value = "";
            fetchData();
        });

        // Apply date filter
        document.getElementById("applyDateFilter").addEventListener("click", function () {
            fetchData();
        });

        // Event listener for Reset Sessions button with error trapping
        document.getElementById("resetSessions").addEventListener("click", function () {
            Notiflix.Confirm.show(
                'Reset Sessions',
                'Are you sure you want to reset sessions for all students?',
                'Yes',
                'No',
                function() {
                    fetch("reset_sessions.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" }
                    })
                    .then(response => response.text())
                    .then(text => {
                        if (!text) {
                            throw new Error("Empty response received from server.");
                        }
                        try {
                            let jsonResponse = JSON.parse(text);
                            if (jsonResponse.success) {
                                Notiflix.Notify.success(jsonResponse.message);
                                fetchData();
                            } else {
                                Notiflix.Notify.failure("Error: " + jsonResponse.message);
                            }
                        } catch (e) {
                            console.error("Error parsing JSON:", text);
                            throw e;
                        }
                    })
                    .catch(error => {
                        console.error("Fetch error:", error);
                        Notiflix.Notify.failure("There was an error processing the reset request.");
                    });
                }
            );
        });

        // Initial fetch when the page loads
        fetchData();
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

