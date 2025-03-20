<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/config.php';
require_once '../shared/header.php';
?>

<div class="p-6 ml-64 bg-white min-h-screen">
    <?php include '../shared/aside.php'; ?>

    <h2 class="text-2xl font-semibold text-darkblue mb-6">Sit-in Records</h2>

    <!-- Search & Filters -->
    <div class="flex gap-4 mb-6">
        <input type="text" id="searchName" placeholder="Search by Student Name"
               class="p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-steelblue w-1/3">
        <input type="text" id="searchLab" placeholder="Search by Lab"
               class="p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-steelblue w-1/3">
        <button id="clearFilters" class="bg-darkblue text-white px-5 py-3 rounded-md hover:bg-navy">
            Clear
        </button>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto bg-white">
        <table class="w-full border-collapse">
            <thead class="bg-darkblue text-white">
                <tr>
                    <th class="p-4">Student ID</th>
                    <th class="p-4">Student Name</th>
                    <th class="p-4">Lab</th>
                    <th class="p-4">Reason</th>
                    <th class="p-4">Status</th>
                    <th class="p-4">Session</th>
                    <th class="p-4">Action</th>
                </tr>
            </thead>
            <tbody id="sitInTable" class="text-gray-700"></tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div id="pagination" class="flex justify-center mt-6 space-x-2"></div>
</div>

<?php require_once '../shared/footer.php'; ?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
    // Function to fetch sit-in records data
    function fetchData(page = 1) {
        let name = document.getElementById("searchName").value;
        let lab = document.getElementById("searchLab").value;

        fetch(`sit_in_fetch.php?page=${page}&name=${encodeURIComponent(name)}&lab=${encodeURIComponent(lab)}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById("sitInTable").innerHTML = data;
            })
            .catch(error => console.error("Error fetching data:", error));
    }

    // Handle the "End" button click event
    document.getElementById("sitInTable").addEventListener("click", function (event) {
        if (event.target && event.target.classList.contains("end-btn")) {
            let idno = event.target.getAttribute("data-id");

            // Send a POST request to end the sit-in
            fetch("sit_in_end.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `idno=${idno}`
            })
            .then(response => response.json())  // Ensure the response is parsed as JSON
            .then(jsonResponse => {
                if (jsonResponse.success) {
                    alert(jsonResponse.message); // Alert success message
                    fetchData(); // Refresh table after successful update
                } else {
                    alert("Error: " + jsonResponse.message); // Show error if any
                }
            })
            .catch(error => {
                console.error("Fetch error:", error);
                alert("There was an error processing the request.");
            });
        }
    });

    // Search and filter functionality
    document.getElementById("searchName").addEventListener("input", function () {
        fetchData();
    });

    document.getElementById("searchLab").addEventListener("input", function () {
        fetchData();
    });

    // Clear search filters
    document.getElementById("clearFilters").addEventListener("click", function () {
        document.getElementById("searchName").value = "";
        document.getElementById("searchLab").value = "";
        fetchData();
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
                    navy: "#0D1B2A",
                    darkblue: "#1B263B",
                    steelblue: "#415A77",
                    bluegray: "#778DA9",
                }
            }
        }
    }
</script>
