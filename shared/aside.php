<?php if ($_SESSION['role'] == 'Student'): ?>
<header id="studentHeader" class="fixed top-0 left-0 z-30 w-full backdrop-blur-sm border-b border-gray-200/50 transition-all duration-300">
    <div class="container max-w-[1400px] mx-auto px-4 py-3">
        <div class="flex justify-between items-center">
            <!-- Mobile Menu Button for student -->
            <button id="menuToggle" class="md:hidden p-2 text-gray-600 hover:text-indigo-600 transition-colors">
                <i class="fas fa-bars text-xl"></i>
            </button>
            
            <!-- Desktop Navigation -->
            <nav id="studentNav" class="hidden md:flex items-center space-x-6">
               <a href="home.php" class="flex items-center text-gray-600 hover:text-indigo-600 transition-colors">
                    <i class="fas fa-home mr-2"></i>Home
                </a>
                <a href="profile.php" class="flex items-center text-gray-600 hover:text-indigo-600 transition-colors">
                    <i class="fas fa-user mr-2"></i>Profile
                </a>
                <a href="history.php" class="flex items-center text-gray-600 hover:text-indigo-600 transition-colors">
                    <i class="fas fa-history mr-2"></i>History
                </a>
                <a href="reservation.php" class="flex items-center text-gray-600 hover:text-indigo-600 transition-colors">
                    <i class="fas fa-calendar-check mr-2"></i>Reservation
                </a>
            </nav>
            
            <!-- Logout Button -->
            <a href="../logout.php" class="flex items-center text-red-600 hover:text-red-700 transition-colors">
                <i class="fas fa-sign-out-alt mr-2"></i>Log Out
            </a>
        </div>
    </div>
        <!-- Mobile Navigation -->

    <nav id="studentNav" class="md:hidden absolute top-full left-0 w-full bg-white border-b border-gray-200/50 backdrop-blur-sm hidden transition-all duration-300">
        <div class="container max-w-[1400px] mx-auto px-4 py-2">
            <a href="home.php" class="flex items-center py-3 text-gray-600 hover:text-indigo-600 transition-colors">
                <i class="fas fa-home mr-3 w-6"></i>Home
            </a>
            <a href="profile.php" class="flex items-center py-3 text-gray-600 hover:text-indigo-600 transition-colors">
                <i class="fas fa-user mr-3 w-6"></i>Profile
            </a>
            <a href="history.php" class="flex items-center py-3 text-gray-600 hover:text-indigo-600 transition-colors">
                <i class="fas fa-history mr-3 w-6"></i>History
            </a>
            <a href="reservation.php" class="flex items-center py-3 text-gray-600 hover:text-indigo-600 transition-colors">
                <i class="fas fa-calendar-check mr-3 w-6"></i>Reservation
            </a>
        </div>
    </nav>
</header>

<script>
    // script for the student header
    window.addEventListener('scroll', function () {
        const header = document.getElementById('studentHeader');
        if (window.scrollY > 50) {
            header.classList.add('bg-white/80');
        } else {
            header.classList.remove('bg-white/80');
        }
    });
    document.getElementById('menuToggle').addEventListener('click', function () {
        let menu = document.getElementById('studentNav');
        menu.classList.toggle('hidden');
        }
    });
</script>
<?php endif; ?>
<!-- Admin -->
<?php if ($_SESSION['role'] == 'Admin'): ?>
<header id="adminHeader" class="fixed top-0 left-0 z-30 w-full backdrop-blur-sm border-b border-gray-200/50 transition-all duration-300">
    <div class="container max-w-[1400px] mx-auto px-4 py-3">
        <div class="flex justify-between items-center">
            <!-- Mobile Menu Button -->
            <button id="adminMenuToggle" class="md:hidden p-2 text-gray-600 hover:text-indigo-600 transition-colors">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <div class="flex items-center space-x-3 ">
                <img src="logo.png" alt="Logo" class="h-10 w-10 rounded-lg md:hidden">
                <h2 class="text-xl font-semibold text-gray-800 md:hidden">Admin Panel</h2>
            </div>

            <!-- Desktop Navigation -->
            <nav id="adminNav" class="hidden md:flex items-center space-x-6">
               <div class="flex items-center space-x-3 ">
               <img src="logo.png" alt="Logo" class="h-10 w-10 rounded-lg hidden md:block ">
               <h2 class="text-xl font-semibold text-gray-800 hidden md:block ">Admin Panel</h2>
            </div>
               
            </nav>
            <!-- Logout Button -->
            <a href="../logout.php" class="flex items-center text-red-600 hover:text-red-700 transition-colors">
                <i class="fas fa-sign-out-alt mr-2"></i>Log Out
            </a>
        </div>
    </div>

   <!-- Mobile Navigation -->
    <nav id="adminMenu" class="md:hidden absolute top-full left-0 w-full bg-white border-b border-gray-200/50 backdrop-blur-sm hidden transition-all duration-300">
        <div class="container max-w-[1400px] mx-auto px-4 py-2">
       
        <!-- Navigation -->
        <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
            <ul class="space-y-1">
                <li>
                    <a href="dashboard.php" class="flex items-center px-4 py-3 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                        <i class="fas fa-chart-bar mr-3 w-6"></i>Dashboard
                    </a>
                </li>
                <li>
                    <a href="manage_users.php" class="flex items-center px-4 py-3 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                        <i class="fas fa-users mr-3 w-6"></i>Manage Users
                    </a>
                </li>
                <li>
                    <a href="reports.php" class="flex items-center px-4 py-3 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                        <i class="fas fa-file-alt mr-3 w-6"></i>Reports
                    </a>
                </li>
                <li>
                    <a href="sitting_records.php" class="flex items-center px-4 py-3 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                        <i class="fas fa-chair mr-3 w-6"></i>Sitting Records
                    </a>
                </li>
                <li>
                    <button id="openSearchModal" class="w-full flex items-center px-4 py-3 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                        <i class="fas fa-search mr-3 w-6"></i>Search Student
                    </button>
                    <a href="admin_feedback.php" class="flex items-center px-4 py-3 text-gray-600 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                        <i class="fas fa-comments mr-3 w-6"></i>View Feedback
                    </a>
                </li>
            </ul>
        </nav>\

        </div>
</nav>
</header>
<script>
        // script for the admin header
    document.getElementById('adminMenuToggle').addEventListener('click', function () {
                let menu = document.getElementById('adminMenu');
                menu.classList.toggle('hidden');
            });
       
</script>


<?php endif; ?>

<!-- Search Modal -->
<div id="searchModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center hidden transition-all duration-300">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-md mx-4 transform transition-all">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-search text-indigo-600 mr-2"></i>Search Student ID
                </h3>
                <button id="cancelButton" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="space-y-4">
                <input type="text" id="searchInput" 
                       class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" 
                       placeholder="Enter Student ID">
                
                <div id="searchResult" class="text-sm text-gray-600"></div>
                
                <div id="studentDetails" class="space-y-3 hidden">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                        <input type="text" id="fullName" 
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" 
                               readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Remaining Sessions</label>
                        <input type="text" id="remainingSessions" 
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 bg-gray-50 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all" 
                               readonly>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                        <select id="reason" name="reason" 
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                            <option value="">Select Reason</option>
                            <option value="C programming">C programming</option>
                            <option value="C# programming">C# programming</option>
                            <option value="Java programming">Java programming</option>
                            <option value="PHP programming">PHP programming</option>
                            <option value="Database">Database</option>
                            <option value="Digital Logic & Design">Digital Logic & Design</option>
                            <option value="Embedded Systems & IoT">Embedded Systems & IoT</option>
                            <option value="Python Programming">Python Programming</option>
                            <option value="Systems Integration & Architecture">Systems Integration & Architecture</option>
                            <option value="Computer Application">Computer Application</option>
                            <option value="Web Design & Development">Web Design & Development</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lab</label>
                        <select id="lab" 
                               class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                            <option value="">Select Lab</option>
                            <option value="Lab 517">Lab 517</option>
                            <option value="Lab 524">Lab 524</option>
                            <option value="Lab 526">Lab 526</option>
                            <option value="Lab 528">Lab 528</option>
                            <option value="Lab 530">Lab 530</option>
                            <option value="Lab 542">Lab 542</option>
                            <option value="Lab 544">Lab 544</option>
                        </select>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button id="cancelButton" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                        Cancel
                    </button>
                    <button id="sittingButton" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors hidden">
                        <i class="fas fa-chair mr-2"></i>Sitting
                    </button>
                    <button id="searchButton" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="fas fa-search mr-2"></i>Search
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.5/dist/notiflix-aio-3.2.5.min.js"></script>

<script>
    document.getElementById('openSearchModal').addEventListener('click', function () {
        document.getElementById('searchModal').classList.remove('hidden');
    });
    document.getElementById('cancelButton').addEventListener('click', function () {
        document.getElementById('searchModal').classList.add('hidden');
        document.getElementById('searchResult').innerHTML = ""; // Clear previous result
        document.getElementById('studentDetails').classList.add('hidden'); // Hide student details
        document.getElementById('sittingButton').classList.add('hidden'); // Hide Sitting button
        document.getElementById('searchButton').classList.remove('hidden'); // Show Search button again
    });
    document.getElementById('searchButton').addEventListener('click', function () {
        let studentIDNO = document.getElementById('searchInput').value.trim();
        let searchResult = document.getElementById('searchResult');
        let studentDetails = document.getElementById('studentDetails');
        let fullName = document.getElementById('fullName');
        let remainingSessions = document.getElementById('remainingSessions');
        let reason = document.getElementById('reason');
        let lab = document.getElementById('lab');
        let cancelButton = document.getElementById('cancelButton');
        let sittingButton = document.getElementById('sittingButton');
        let searchButton = document.getElementById('searchButton');

        if (studentIDNO) {
            fetch('search_student.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'idno=' + encodeURIComponent(studentIDNO) // Change 'id' to 'idno'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "found") {
                    searchResult.innerHTML = ''; // Clear previous results
                    fullName.value = data.full_name; // Populate full name
                    remainingSessions.value = data.remaining_sessions; // Populate remaining sessions
                    reason.value = ''; // Clear reason field (or set a default if you have one)
                    lab.value = ''; // Clear lab field (or set a default if you have one)
                    studentDetails.classList.remove('hidden'); // Show student details

                    // Hide Search button, show Cancel and Sitting buttons
                    searchButton.classList.add('hidden');
                    cancelButton.classList.remove('hidden');
                    sittingButton.classList.remove('hidden');
                } else {
                    // Replacing innerHTML message with Notiflix notification
                    // searchResult.innerHTML = '<p class="text-danger">Student not found.</p>';
                    Notiflix.Notify.failure('Student not found.');
                    studentDetails.classList.add('hidden'); // Hide student details if not found
                    sittingButton.classList.add('hidden'); // Hide Sitting button if not found
                    cancelButton.classList.add('hidden'); // Hide Cancel button if not found
                    searchButton.classList.remove('hidden'); // Show Search button again
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Replace error message with Notiflix notification
                // searchResult.innerHTML = '<p class="text-danger">Error fetching data.</p>';
                Notiflix.Notify.failure('Error fetching data.');
                studentDetails.classList.add('hidden'); // Hide student details on error
                sittingButton.classList.add('hidden'); // Hide Sitting button on error
                cancelButton.classList.add('hidden'); // Hide Cancel button on error
                searchButton.classList.remove('hidden'); // Show Search button again
            });
        } else {
            // Replace alert with Notiflix for validation
            // alert('Please enter a valid Student ID Number.');
            Notiflix.Notify.warning('Please enter a valid Student ID Number.');
        }
    });
    document.getElementById('sittingButton').addEventListener('click', function () {
        let studentIDNO = document.getElementById('searchInput').value.trim();
        let fullName = document.getElementById('fullName').value.trim();
        let lab = document.getElementById('lab').value.trim();
        let reason = document.getElementById('reason').value.trim();
        let remainingSessionsField = document.getElementById('remainingSessions');

        if (studentIDNO && fullName && lab && reason) {
            fetch('sitting_process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `idno=${encodeURIComponent(studentIDNO)}&full_name=${encodeURIComponent(fullName)}&lab=${encodeURIComponent(lab)}&reason=${encodeURIComponent(reason)}`
            })
            .then(response => response.json()) // Expecting JSON response
            .then(data => {
                if (data.status === "success") {
                    // Replace alert with Notiflix notification
                    // alert('Sitting session recorded successfully!');
                    Notiflix.Notify.success('Sitting session recorded successfully!');
                    remainingSessionsField.value = data.remaining_sessions;
                    document.getElementById('searchModal').classList.add('hidden');
                } else {
                    // Replace alert with Notiflix notification
                    // alert(data.message);
                    Notiflix.Notify.failure(data.message);
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                // Replace alert with Notiflix notification
                // alert('Error processing request.');
                Notiflix.Notify.failure('Error processing request.');
            });
        } else {
            // Replace alert with Notiflix notification
            // alert('Please fill in all fields.');
            Notiflix.Notify.warning('Please fill in all fields.');
        }
    });
</script>

