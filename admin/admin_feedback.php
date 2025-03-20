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

    <h2 class="text-2xl font-semibold text-darkblue mb-6">Feedback Records</h2>

    <!-- Search & Filters -->
    <div class="flex gap-4 mb-6">
        <input type="text" id="searchLab" placeholder="Search by Lab"
               class="p-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-steelblue w-1/3">
        <input type="text" id="searchIdno" placeholder="Search by Student ID"
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
                    <th class="p-4">Feedback ID</th>
                    <th class="p-4">Student ID</th>
                    <th class="p-4">Lab</th>
                    <th class="p-4">Date</th>
                    <th class="p-4">Message</th>
                </tr>
            </thead>
            <tbody id="feedbackTable" class="text-gray-700"></tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div id="pagination" class="flex justify-center mt-6 space-x-2"></div>
</div>

<?php require_once '../shared/footer.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    function fetchFeedback(page = 1) {
        let lab = document.getElementById("searchLab").value;
        let idno = document.getElementById("searchIdno").value;

        fetch(`fetch_feedback.php?page=${page}&lab=${encodeURIComponent(lab)}&idno=${encodeURIComponent(idno)}`)
            .then(response => response.text())
            .then(data => {
                document.getElementById("feedbackTable").innerHTML = data;
            })
            .catch(error => console.error("Error fetching data:", error));
    }

    document.getElementById("searchLab").addEventListener("input", fetchFeedback);
    document.getElementById("searchIdno").addEventListener("input", fetchFeedback);

    document.getElementById("clearFilters").addEventListener("click", function () {
        document.getElementById("searchLab").value = "";
        document.getElementById("searchIdno").value = "";
        fetchFeedback();
    });

    fetchFeedback();
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