<?php if ($_SESSION['role'] == 'Student'): ?>
<header class="bg-navy text-offwhite p-4 shadow-md flex justify-between items-center relative">
    <!-- Logo & Title -->
    <div class="flex items-center space-x-2">
        <img src="logo.png" alt="Logo" class="h-8">
        <h1 class="text-lg font-medium">Student Panel</h1>
    </div>

    <!-- Mobile Menu Button -->
    <button id="menuToggle" class="md:hidden p-2 text-offwhite focus:outline-none">
        ☰
    </button>

    <!-- Navigation -->
    <nav id="studentNav"
        class="absolute md:relative top-full left-0 w-full md:w-auto bg-navy md:bg-transparent md:flex flex-col md:flex-row md:space-x-4 shadow-md md:shadow-none hidden md:block transition-all duration-300">
        <a href="home.php" class="block py-2 px-4 hover:text-bluegray transition">Home</a>
        <a href="profile.php" class="block py-2 px-4 hover:text-bluegray transition">Profile</a>
        <a href="history.php" class="block py-2 px-4 hover:text-bluegray transition">History</a>
        <a href="reservation.php" class="block py-2 px-4 hover:text-bluegray transition">Reservation</a>
        <a href="../logout.php" class="block py-2 px-4 text-danger hover:text-red-700 transition">Log Out</a>
    </nav>
</header>

<script>
    document.getElementById('menuToggle').addEventListener('click', function () {
        let menu = document.getElementById('studentNav');
        menu.classList.toggle('hidden');
    });
</script>
<?php endif; ?>


<?php if ($_SESSION['role'] == 'Admin'): ?>
<aside class="z-30 fixed h-screen top-0 left-0 bg-navy w-60 p-5 shadow-md flex flex-col">
    <!-- Logo Section -->
    <div class="flex items-center space-x-2 mb-6">
        <img src="logo.png" alt="Logo" class="h-10"> 
        <h2 class="text-xl font-semibold text-white">Admin Panel</h2>
    </div>

    <!-- Navigation -->
    <ul class="space-y-2 flex-1">
        <li>
            <a href="dashboard.php" class="block p-3 text-white hover:bg-bluegray hover:bg-opacity-80 rounded-md transition">
                📊 Dashboard
            </a>
        </li>
        <li>
            <a href="manage_users.php" class="block p-3 text-white hover:bg-bluegray hover:bg-opacity-80 rounded-md transition">
                👥 Manage Users
            </a>
        </li>
        <li>
            <a href="reports.php" class="block p-3 text-white hover:bg-bluegray hover:bg-opacity-80 rounded-md transition">
                📁 Reports
            </a>
        </li>
        <li>
            <a href="sitting_records.php" class="block p-3 text-white hover:bg-bluegray hover:bg-opacity-80 rounded-md transition">
                🪑 Sitting Records
            </a>
        </li>
        <li>
            <button id="openSearchModal" class="w-full text-left block p-3 text-white hover:bg-bluegray hover:bg-opacity-80 rounded-md transition">
                🔍 Search Student
            </button>
        </li>
        <li>
            <a href="admin_feedback.php" class="block p-3 text-white hover:bg-bluegray hover:bg-opacity-80 rounded-md transition">
                📝 View Feedback
            </a>
        </li>

    </ul>

    <!-- Logout Button -->
    <a href="../logout.php" class="block p-3 text-white bg-red-600 hover:bg-red-700 rounded-md transition">
        🚪 Log Out
    </a>
</aside>
<?php endif; ?>



<div id="searchModal" class="z-50 fixed inset-0 bg-darkblue bg-opacity-80 flex items-center justify-center hidden">
    <div class="bg-white p-4 rounded shadow-md w-80">
        <h3 class="text-lg font-medium text-navy mb-4">Search Student ID</h3>
        <input type="text" id="searchInput" class="w-full p-2 border border-steelblue rounded focus:outline-none focus:ring-1 focus:ring-bluegray mb-4" placeholder="Enter Student ID">
        <div id="searchResult" class="mt-3 text-navy"></div>
        <div id="studentDetails" class="mt-4 hidden">
            <div class="mb-2">
                <label for="fullName" class="block text-sm font-medium text-gray-700">Full Name</label>
                <input type="text" id="fullName" class="w-full p-2 border border-steelblue rounded bg-gray-100" placeholder="Full Name" readonly>
            </div>

            <div class="mb-2">
                <label for="remainingSessions" class="block text-sm font-medium text-gray-700">Remaining Sessions</label>
                <input type="text" id="remainingSessions" class="w-full p-2 border border-steelblue rounded bg-gray-100" placeholder="Remaining Sessions" readonly>
            </div>

            <div class="mb-2">
                <label for="reason" class="block text-sm font-medium text-gray-700">Reason</label>
                <input type="text" id="reason" name="reason" class="w-full p-2 border border-steelblue rounded" placeholder="Enter Reason">
            </div>

            <div class="mb-2">
                <label for="lab" class="block text-sm font-medium text-gray-700">Lab</label>
                <input type="text" id="lab" class="w-full p-2 border border-steelblue rounded" placeholder="Enter Lab">
            </div>
        </div>
        <div class="mt-3 flex justify-end space-x-2">
            <button id="cancelButton" class="px-4 py-2 bg-bluegray text-white rounded hover:bg-steelblue hidden">Cancel</button>
            <button id="sittingButton" class="px-4 py-2 bg-steelblue text-white rounded hover:bg-darkblue hidden">Sitting</button>
            <button id="searchButton" class="px-4 py-2 bg-steelblue text-white rounded hover:bg-darkblue">Search</button>
        </div>
    </div>
</div>  

<script>    
    document.getElementById('openSearchModal').addEventListener('click', function() {
        document.getElementById('searchModal').classList.remove('hidden');
    });

    document.getElementById('cancelButton').addEventListener('click', function() {
        document.getElementById('searchModal').classList.add('hidden');
        document.getElementById('searchResult').innerHTML = ""; // Clear previous result
        document.getElementById('studentDetails').classList.add('hidden'); // Hide student details
        document.getElementById('sittingButton').classList.add('hidden'); // Hide Sitting button
        document.getElementById('searchButton').classList.remove('hidden'); // Show Search button again
    });

    document.getElementById('searchButton').addEventListener('click', function() {
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
                    searchResult.innerHTML = '<p class="text-danger">Student not found.</p>';
                    studentDetails.classList.add('hidden'); // Hide student details if not found
                    sittingButton.classList.add('hidden'); // Hide Sitting button if not found
                    cancelButton.classList.add('hidden'); // Hide Cancel button if not found
                    searchButton.classList.remove('hidden'); // Show Search button again
                }
            })
            .catch(error => {
                console.error('Error:', error);
                searchResult.innerHTML = '<p class="text-danger">Error fetching data.</p>';
                studentDetails.classList.add('hidden'); // Hide student details on error
                sittingButton.classList.add('hidden'); // Hide Sitting button on error
                cancelButton.classList.add('hidden'); // Hide Cancel button on error
                searchButton.classList.remove('hidden'); // Show Search button again
            });
        } else {
            alert('Please enter a valid Student ID Number.');
        }
    });
    
    document.getElementById('sittingButton').addEventListener('click', function() {
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
                alert('Sitting session recorded successfully!');
                remainingSessionsField.value = data.remaining_sessions;
                document.getElementById('searchModal').classList.add('hidden');
            } else {
                // Handle the error (duplicate session)
                alert(data.message);  // Display the error message returned from the server
            }
        })
        .catch(error => {
            console.error('Fetch Error:', error);
            alert('Error processing request.');
        });
    } else {
        alert('Please fill in all fields.');
    }
});





</script>

