<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config/config.php';
require_once '../shared/header.php';

// Fetch lab data
$labQuery = "SELECT lab, COUNT(*) as count FROM sit_in GROUP BY lab";
$labResult = mysqli_query($conn, $labQuery);
$labs = [];
$labCounts = [];
while ($row = mysqli_fetch_assoc($labResult)) {
    $labs[] = $row['lab'];
    $labCounts[] = $row['count'];
}

// Fetch reason data
$reasonQuery = "SELECT reason, COUNT(*) as count FROM sit_in GROUP BY reason";
$reasonResult = mysqli_query($conn, $reasonQuery);
$reasons = [];
$reasonCounts = [];
while ($row = mysqli_fetch_assoc($reasonResult)) {
    $reasons[] = $row['reason'];
    $reasonCounts[] = $row['count'];
}
?>

<div class="p-6 ml-64 bg-white min-h-screen">
    <?php include '../shared/aside.php'; ?>

    <h2 class="text-2xl font-semibold text-darkblue mb-6">Current Sit-in Records</h2>

    <!-- Search Bar -->
    <div class="mb-4">
        <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search records..." class="w-full p-2 border border-gray-300 rounded-md">
    </div>

    <!-- Button to Export Excel -->
    <div class="mb-6">
        <button id="exportExcel" class="px-4 py-2 bg-green-500 text-white rounded">Export Excel</button>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-center">Sit-ins per Lab</h3>
            <canvas id="labChart" class="max-w-xs mx-auto"></canvas>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold text-center">Sit-ins per Reason</h3>
            <canvas id="reasonChart" class="max-w-xs mx-auto"></canvas>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto bg-white">
        <table class="w-full border-collapse" id="sitInTable">
            <thead class="bg-darkblue text-white">
                <tr>
                    <th class="p-4">Student ID</th>
                    <th class="p-4">Student Name</th>
                    <th class="p-4">Reason</th>
                    <th class="p-4">Lab</th>
                    <th class="p-4">In Time</th>
                    <th class="p-4">Out Time</th>
                    <th class="p-4">Date</th>
                </tr>
            </thead>
            <tbody id="sitInTableBody" class="text-gray-700">
                <?php include 'current_sit_in_fetch.php'; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.1/xlsx.full.min.js"></script>

<script>
    // Lab Chart
    const labCtx = document.getElementById('labChart').getContext('2d');
    new Chart(labCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($labs); ?>,
            datasets: [{
                label: 'Sit-ins per Lab',
                data: <?php echo json_encode($labCounts); ?>,
                backgroundColor: ['#4CAF50', '#FF9800', '#F44336', '#2196F3', '#9C27B0']
            }]
        }
    });

    // Reason Chart
    const reasonCtx = document.getElementById('reasonChart').getContext('2d');
    new Chart(reasonCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode($reasons); ?>,
            datasets: [{
                label: 'Sit-ins per Reason',
                data: <?php echo json_encode($reasonCounts); ?>,
                backgroundColor: ['#E91E63', '#03A9F4', '#8BC34A', '#FFEB3B', '#FF5722']
            }]
        }
    });

    // Search Function (Filters Table Rows)
    function filterTable() {
        let input = document.getElementById("searchInput").value.toLowerCase();
        let table = document.getElementById("sitInTable");
        let rows = table.getElementsByTagName("tr");

        for (let i = 0; i < rows.length; i++) {
            let rowText = rows[i].textContent.toLowerCase();
            rows[i].style.display = rowText.includes(input) ? "" : "none";
        }
    }

    // Export to Excel
    document.getElementById("exportExcel").addEventListener("click", function() {
        let table = document.getElementById("sitInTable");
        let rows = table.querySelectorAll("tr");
        let excelData = [];

        // Extract header row
        let headers = [];
        rows[0].querySelectorAll("th").forEach(cell => {
            headers.push(cell.innerText);
        });
        excelData.push(headers);

        // Extract rows
        rows.forEach((row, index) => {
            if (index > 0) {
                let rowData = [];
                row.querySelectorAll("td").forEach(cell => {
                    rowData.push(cell.innerText);
                });
                excelData.push(rowData);
            }
        });

        // Create an Excel file
        let wb = XLSX.utils.book_new();
        let ws = XLSX.utils.aoa_to_sheet(excelData);
        XLSX.utils.book_append_sheet(wb, ws, "Sit-in Records");

        // Download Excel
        XLSX.writeFile(wb, "sit_in_records.xlsx");
    });
</script>

<?php require_once '../shared/footer.php'; ?>
