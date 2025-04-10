<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SITIn Application</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:ital,wght@0,100..900;1,100..900&family=Raleway:ital,wght@0,100..900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Noto Sans', sans-serif;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Raleway', sans-serif;
        }
    </style>
</head>
<body class="min-h-screen -gray-100 text-gray-900">

<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    navy: "#0D1B2A",
                    darkblue: "#1B263B",
                    steelblue: "#415A77",
                    bluegray: "#778DA9",
                    offwhite: "#E0E1DD",
                }
            }
        }
    }
</script>

</body>
</html>

<div class="container mx-auto p-4 flex">
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
    let reason = document.getElementById('reason').value.trim(); // ✅ Ensure reason is included
    let remainingSessionsField = document.getElementById('remainingSessions');

    if (studentIDNO && fullName && lab && reason) { // ✅ Check all fields
        fetch('sitting_process.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `idno=${encodeURIComponent(studentIDNO)}&full_name=${encodeURIComponent(fullName)}&lab=${encodeURIComponent(lab)}&reason=${encodeURIComponent(reason)}`
        })
        .then(response => response.text()) // Inspect raw response first
        .then(text => {
            console.log("Raw Response:", text); // Log raw response
            let data;
            try {
                data = JSON.parse(text);
            } catch (error) {
                console.error("JSON Parse Error:", error);
                alert("Invalid response from server.");
                return;
            }

            if (data.status === "success") {
                alert('Sitting recorded successfully!');
                remainingSessionsField.value = data.remaining_sessions;
                document.getElementById('searchModal').classList.add('hidden');
            } else {
                console.error("Server Error:", data.message);
                alert(data.message);
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

    <main class="pl-72 p-4 w-full">
        <h1 class="text-4xl font-bold mb-4">History</h1>
        <p class="text-gray-700 mb-4">Welcome to your history, <span class="font-semibold">1as</span>!</p>
        
        <!-- Report Button -->
        <div class="mb-4">
            <a href="feedback.php" class="bg-gray-800 text-white px-4 py-2 rounded-lg hover:bg-gray-900 transition duration-200">
                Report Issue
            </a>
        </div>

        <!-- Sit-In History Table -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-2xl font-semibold text-gray-800 mb-4">Sit-In History</h2>

                            <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 border-b">Lab</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 border-b">Reason</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 border-b">In Time</th>
                                <th class="px-6 py-3 text-left text-sm font-medium text-gray-700 border-b">Out Time</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-700">
                                                            <tr class="hover:bg-gray-50 transition duration-200">
                                    <td class="px-6 py-3 border-b">213</td>
                                    <td class="px-6 py-3 border-b"></td>
                                    <td class="px-6 py-3 border-b">2025-03-17 12:42:58</td>
                                    <td class="px-6 py-3 border-b">2025-03-19 16:10:05</td>
                                </tr>
                                                            <tr class="hover:bg-gray-50 transition duration-200">
                                    <td class="px-6 py-3 border-b">232</td>
                                    <td class="px-6 py-3 border-b"></td>
                                    <td class="px-6 py-3 border-b">2025-03-19 15:13:49</td>
                                    <td class="px-6 py-3 border-b">2025-03-19 15:59:02</td>
                                </tr>
                                                            <tr class="hover:bg-gray-50 transition duration-200">
                                    <td class="px-6 py-3 border-b">213</td>
                                    <td class="px-6 py-3 border-b"></td>
                                    <td class="px-6 py-3 border-b">2025-03-19 16:19:50</td>
                                    <td class="px-6 py-3 border-b"></td>
                                </tr>
                                                            <tr class="hover:bg-gray-50 transition duration-200">
                                    <td class="px-6 py-3 border-b">213</td>
                                    <td class="px-6 py-3 border-b"></td>
                                    <td class="px-6 py-3 border-b">2025-03-19 16:21:56</td>
                                    <td class="px-6 py-3 border-b">2025-03-19 16:22:07</td>
                                </tr>
                                                            <tr class="hover:bg-gray-50 transition duration-200">
                                    <td class="px-6 py-3 border-b">213</td>
                                    <td class="px-6 py-3 border-b">sdasdasd</td>
                                    <td class="px-6 py-3 border-b">2025-03-19 17:14:48</td>
                                    <td class="px-6 py-3 border-b"></td>
                                </tr>
                                                    </tbody>
                    </table>
                </div>
                    </div>
    </main>
</div>

    <footer class="bg-white shadow-md p-4 text-center w-full fixed bottom-0 
        ">
        <div class="container mx-auto px-4">
            <p class="text-gray-700">&copy; 2023 SITIn Application. All rights reserved.</p>
        </div>
    </footer>
