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
<div class="mt-10 flex min-h-screen bg-gray-50 text-gray-900">
    <?php include '../shared/aside.php'; ?>
    <main class="flex-1 pt-10 -6">
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

        // Handle the table button click events with improved error handling
        document.getElementById("sitInTable").addEventListener("click", function (event) {
            // For end button
            if (event.target && (event.target.classList.contains("end-btn") || 
                                (event.target.parentElement && event.target.parentElement.classList.contains("end-btn")))) {
                // Make sure we have the button element (could be the icon inside the button)
                const button = event.target.classList.contains("end-btn") 
                    ? event.target 
                    : event.target.parentElement;
                    
                let idno = button.getAttribute("data-id");
                
                if (!idno) {
                    Notiflix.Notify.failure("Invalid Student ID.");
                    return;
                }
                
                // Confirm before ending the session
                Notiflix.Confirm.show(
                    'End Session',
                    `Are you sure you want to end the active session for student ID: ${idno}?`,
                    'Yes',
                    'No',
                    function() {
                        // Store the original button content
                        const originalContent = button.innerHTML;
                        
                        // Disable button and show loading state
                        button.disabled = true;
                        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Ending...';
                        
                        // Send AJAX request to sitting_process.php with action=end
                        fetch("sitting_process.php", {
                            method: "POST",
                            headers: { "Content-Type": "application/x-www-form-urlencoded" },
                            body: `idno=${idno}&action=end`
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! Status: ${response.status}`);
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.status === 'success') {
                                // Success notification
                                Notiflix.Notify.success(data.message);
                                
                                // Show detailed report with remaining sessions
                                if (data.remaining_sessions !== undefined) {
                                    Notiflix.Report.success(
                                        'Session Ended Successfully',
                                        `The session has been ended. The student now has ${data.remaining_sessions} remaining sessions.`,
                                        'OK'
                                    );
                                }
                                
                                // Refresh the table data to show updated status
                                fetchData();
                            } else {
                                console.error("Error:", data.message);
                                Notiflix.Notify.failure(data.message);
                                
                                // Reset button state
                                button.disabled = false;
                                button.innerHTML = originalContent;
                            }
                        })
                        .catch(error => {
                            console.error("Fetch error:", error);
                            Notiflix.Notify.failure("Failed to connect to server: " + error.message);
                            
                            // Reset button state
                            button.disabled = false;
                            button.innerHTML = originalContent;
                        });
                    }
                );
            }
            
            // For reset session button
            if (event.target && (event.target.classList.contains("reset-session-btn") || 
                                 (event.target.parentElement && event.target.parentElement.classList.contains("reset-session-btn")))) {
                // Make sure we have the button element (could be the icon inside the button)
                const button = event.target.classList.contains("reset-session-btn") 
                    ? event.target 
                    : event.target.parentElement;
                    
                let idno = button.getAttribute("data-id");
                let studentName = button.getAttribute("data-name");
                
                if (!idno) {
                    Notiflix.Notify.failure("Invalid Student ID.");
                    return;
                }
                
                // Confirm before resetting sessions for individual student
                Notiflix.Confirm.show(
                    'Reset Sessions',
                    `Are you sure you want to reset sessions for ${studentName} (ID: ${idno})?`,
                    'Yes',
                    'No',
                    function() {
                        // Store the original button content
                        const originalContent = button.innerHTML;
                        
                        // Disable button and show loading state
                        button.disabled = true;
                        button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Processing...';
                        
                        fetch("reset_student_session.php", {
                            method: "POST",
                            headers: { "Content-Type": "application/x-www-form-urlencoded" },
                            body: `idno=${idno}`
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`HTTP error! Status: ${response.status}`);
                            }
                            return response.text();
                        })
                        .then(text => {
                            if (!text) {
                                throw new Error("Empty response received from server.");
                            }
                            
                            try {
                                let jsonResponse = JSON.parse(text);
                                
                                if (jsonResponse.success) {
                                    // If we have old and new values, show a more detailed success message
                                    if (jsonResponse.old_value !== undefined && jsonResponse.new_value !== undefined) {
                                        Notiflix.Report.success(
                                            'Session Reset Successful',
                                            `Sessions for ${studentName} have been reset from ${jsonResponse.old_value} to ${jsonResponse.new_value}.`,
                                            'OK'
                                        );
                                    } else {
                                        Notiflix.Notify.success(jsonResponse.message);
                                    }
                                    
                                    // Refresh table data to show updated sessions
                                    fetchData();
                                } else {
                                    console.error("Server error:", jsonResponse.message);
                                    Notiflix.Notify.failure("Error: " + jsonResponse.message);
                                    
                                    // Reset button state
                                    button.disabled = false;
                                    button.innerHTML = originalContent;
                                }
                            } catch (e) {
                                console.error("Error parsing JSON:", e, "Raw response:", text);
                                Notiflix.Notify.failure("Error processing server response. See console for details.");
                                
                                // Reset button state
                                button.disabled = false;
                                button.innerHTML = originalContent;
                            }
                        })
                        .catch(error => {
                            console.error("Fetch error:", error);
                            Notiflix.Notify.failure("Failed to connect to server: " + error.message);
                            
                            // Reset button state
                            button.disabled = false;
                            button.innerHTML = originalContent;
                        });
                    }
                );
            }
            
            // Handle other buttons (end-btn, points-btn, etc.)
            // Make sure this is integrated with your existing event handlers
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

        // Add event listener for reset student buttons
        document.addEventListener('click', function(event) {
            // Check if the clicked element or its parent has the class 'reset-student-btn' or 'reset-session-btn'
            const resetButton = event.target.closest('.reset-student-btn, .reset-session-btn');
            
            if (resetButton) {
                const idno = resetButton.getAttribute('data-id');
                const studentName = resetButton.getAttribute('data-name') || 'this student';
                
                if (!idno) {
                    Notiflix.Notify.failure('Invalid Student ID');
                    return;
                }
                
                // Show confirmation dialog
                Notiflix.Confirm.show(
                    'Reset Student Sessions',
                    `Are you sure you want to reset sessions to 30 for ${studentName} (ID: ${idno})?`,
                    'Yes',
                    'No',
                    function() {
                        // Show loading state
                        const originalContent = resetButton.innerHTML;
                        resetButton.disabled = true;
                        resetButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Resetting...';
                        
                        // Send AJAX request
                        fetch('reset_student_session.php', {
                            method: 'POST',
                            headers: { "Content-Type": "application/x-www-form-urlencoded" },
                            body: `idno=${idno}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Notiflix.Notify.success(data.message);
                                fetchData(); // Refresh the table
                            } else {
                                Notiflix.Notify.failure(data.message);
                                resetButton.disabled = false;
                                resetButton.innerHTML = originalContent;
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Notiflix.Notify.failure('An error occurred while processing your request');
                            resetButton.disabled = false;
                            resetButton.innerHTML = originalContent;
                        });
                    }
                );
            }
        });
        
        // Add event listener for awarding points
        document.addEventListener('click', function(event) {
            // Check if the clicked element or its parent has the class 'points-btn'
            const pointsButton = event.target.closest('.points-btn');
            
            if (pointsButton) {
                const idno = pointsButton.getAttribute('data-id');
                const sitInId = pointsButton.getAttribute('data-sit-in-id');
                
                if (!idno || !sitInId) {
                    Notiflix.Notify.failure('Invalid parameters for awarding points');
                    return;
                }
                
                // Get selected points from dropdown
                const pointsSelect = pointsButton.parentElement.querySelector('.points-select');
                if (!pointsSelect) {
                    Notiflix.Notify.failure('Points selection not found');
                    return;
                }
                
                const points = pointsSelect.value;
                
                // Show confirmation dialog
                Notiflix.Confirm.show(
                    'Award Points',
                    `Are you sure you want to award ${points} point(s) to student ID: ${idno}?`,
                    'Yes',
                    'No',
                    function() {
                        // Show loading state
                        const originalContent = pointsButton.innerHTML;
                        pointsButton.disabled = true;
                        pointsButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Processing...';
                        
                        // Prepare form data
                        const formData = new FormData();
                        formData.append('sit_in_id', sitInId);
                        formData.append('idno', idno);
                        formData.append('points', points);
                        
                        // Send AJAX request
                        fetch('save_lab_points.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Success notification
                                Notiflix.Notify.success(data.message);
                                
                                // Show detailed report
                                let reportMessage = `${points} point(s) awarded to student. Total points: ${data.total_points}`;
                                
                                // Add session information if applicable
                                if (data.sessions_added) {
                                    reportMessage += `<br><br><strong>${data.sessions_added} session(s) added</strong> because student accumulated ${data.sessions_added * 3} points.`;
                                    reportMessage += `<br>Points reset to ${data.total_points}. Current sessions: ${data.current_sessions}`;
                                }
                                
                                Notiflix.Report.success(
                                    'Points Awarded Successfully',
                                    reportMessage,
                                    'OK'
                                );
                                
                                // Refresh the table data
                                fetchData();
                            } else {
                                console.error('Error:', data.message);
                                Notiflix.Notify.failure(data.message);
                                
                                // Reset button state
                                pointsButton.disabled = false;
                                pointsButton.innerHTML = originalContent;
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Notiflix.Notify.failure('An error occurred while processing your request');
                            pointsButton.disabled = false;
                            pointsButton.innerHTML = originalContent;
                        });
                    }
                );
            }
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

