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

<div class="flex min-h-screen bg-gray-50 text-gray-900 pb-14">
    <?php include '../shared/aside.php'; ?>
    <main class="flex-1 p-4 ml-64">
        <div class="max-w-[1400px] mx-auto">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-semibold text-gray-800">Sit-in Reports</h1>
                    <p class="text-lg text-gray-600">View and analyze sit-in records</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button id="exportPDF" 
                            class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-file-pdf mr-2"></i>Export PDF
                    </button>
                    <button id="printTable" 
                            class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-print mr-2"></i>Print
                    </button>
                    <button id="exportExcel" 
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        <i class="fas fa-file-export mr-2"></i>Export Excel
                    </button>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" 
                               id="searchInput" 
                               onkeyup="if(event.key === 'Enter') filterTable()" 
                               placeholder="Search records..." 
                               class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    </div>
                    <div class="flex space-x-3">
                        <button type="button" 
                                onclick="filterTable()" 
                                class="flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-search mr-2"></i>Search
                        </button>
                        <button type="button" 
                                onclick="clearSearch()" 
                                class="flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                            <i class="fas fa-eraser mr-2"></i>Clear
                        </button>
                    </div>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Sit-ins per Lab</h3>
                        <div class="flex items-center space-x-2">
                            <button class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                    </div>
                    <div class="h-[300px]">
                        <canvas id="labChart" class="w-full h-full"></canvas>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">Sit-ins per Reason</h3>
                        <div class="flex items-center space-x-2">
                            <button class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                    </div>
                    <div class="h-[300px]">
                        <canvas id="reasonChart" class="w-full h-full"></canvas>
                    </div>
                </div>
            </div>

            <!-- Leaderboard Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Student Leaderboard</h3>
                    <div class="flex items-center space-x-2">
                        <button id="refreshLeaderboard" class="p-2 text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Top 3 Podium -->
                <div class="flex justify-center items-end mb-8 h-64">
                    <!-- 2nd Place -->
                    <div class="flex flex-col items-center mx-2">
                        <div class="bg-gray-200 rounded-t-lg p-4 w-24 h-32 flex items-center justify-center">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-gray-400">2</div>
                                <div class="text-sm font-medium text-gray-500" id="secondPlaceName">Loading...</div>
                            </div>
                        </div>
                        <div class="bg-gray-300 w-24 h-8 rounded-b-lg flex items-center justify-center">
                            <span class="text-sm font-bold text-gray-600" id="secondPlacePoints">0 pts</span>
                        </div>
                    </div>
                    
                    <!-- 1st Place -->
                    <div class="flex flex-col items-center mx-2">
                        <div class="bg-yellow-200 rounded-t-lg p-4 w-28 h-40 flex items-center justify-center">
                            <div class="text-center">
                                <div class="text-4xl font-bold text-yellow-500">1</div>
                                <div class="text-sm font-medium text-gray-700" id="firstPlaceName">Loading...</div>
                            </div>
                        </div>
                        <div class="bg-yellow-300 w-28 h-8 rounded-b-lg flex items-center justify-center">
                            <span class="text-sm font-bold text-gray-700" id="firstPlacePoints">0 pts</span>
                        </div>
                    </div>
                    
                    <!-- 3rd Place -->
                    <div class="flex flex-col items-center mx-2">
                        <div class="bg-amber-200 rounded-t-lg p-4 w-24 h-24 flex items-center justify-center">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-amber-600">3</div>
                                <div class="text-sm font-medium text-gray-700" id="thirdPlaceName">Loading...</div>
                            </div>
                        </div>
                        <div class="bg-amber-300 w-24 h-8 rounded-b-lg flex items-center justify-center">
                            <span class="text-sm font-bold text-gray-700" id="thirdPlacePoints">0 pts</span>
                        </div>
                    </div>
                </div>
                
                <!-- Full Leaderboard Table -->
                <div class="overflow-x-auto">
                    <table class="w-full table-fixed">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-12">Rank</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Points</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Sessions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="leaderboardTableBody">
                            <tr>
                                <td colspan="5" class="px-4 py-4 text-center text-gray-500">Loading leaderboard data...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full" id="sitInTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lab</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">In Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Out Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="sitInTableBody">
                            <?php include 'current_sit_in_fetch.php'; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Pagination -->
            <div id="pagination" class="flex justify-between items-center mt-6 bg-white p-4 rounded-xl shadow-sm border border-gray-100"></div>
        </div>
    </main>
</div>

<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.1/xlsx.full.min.js"></script>

<!-- Include html2pdf library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

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
                backgroundColor: [
                    'rgba(99, 102, 241, 0.6)',
                    'rgba(16, 185, 129, 0.6)',
                    'rgba(245, 158, 11, 0.6)',
                    'rgba(239, 68, 68, 0.6)',
                    'rgba(139, 92, 246, 0.6)'
                ],
                borderColor: [
                    'rgba(99, 102, 241, 1)',
                    'rgba(16, 185, 129, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(239, 68, 68, 1)',
                    'rgba(139, 92, 246, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        padding: 20
                    }
                }
            }
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
                backgroundColor: [
                    'rgba(99, 102, 241, 0.6)',
                    'rgba(16, 185, 129, 0.6)',
                    'rgba(245, 158, 11, 0.6)',
                    'rgba(239, 68, 68, 0.6)',
                    'rgba(139, 92, 246, 0.6)'
                ],
                borderColor: [
                    'rgba(99, 102, 241, 1)',
                    'rgba(16, 185, 129, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(239, 68, 68, 1)',
                    'rgba(139, 92, 246, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        padding: 20
                    }
                }
            }
        }
    });

    // Search Function (Filters Table Rows) with debounce
    let searchTimeout;
    function debounceSearch(func, wait) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(func, wait);
    }

    function filterTable() {
        let input = document.getElementById("searchInput").value.toLowerCase();
        let table = document.getElementById("sitInTable");
        let rows = table.getElementsByTagName("tr");

        // Skip the header row
        for (let i = 1; i < rows.length; i++) {
            let rowText = rows[i].textContent.toLowerCase();
            rows[i].style.display = rowText.includes(input) ? "" : "none";
        }
    }

    function clearSearch() {
        document.getElementById("searchInput").value = "";
        filterTable();
    }

    // Print functionality
    document.getElementById("printTable").addEventListener("click", function() {
        // Capture the charts as images
        const labChartImg = document.getElementById('labChart').toDataURL('image/png');
        const reasonChartImg = document.getElementById('reasonChart').toDataURL('image/png');
        
        let printContents = document.getElementById("sitInTable").outerHTML;
        let originalContents = document.body.innerHTML;

        // Add print styles
        let printStyles = `
            <style>
                @media print {
                    @page {
                        size: landscape;
                        margin: 1cm;
                    }
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 12px;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 20px;
                    }
                    th, td {
                        border: 1px solid #ddd;
                        padding: 8px;
                        text-align: left;
                        font-size: 11px;
                    }
                    th {
                        background-color: #f2f2f2;
                        font-weight: bold;
                        color: #333;
                    }
                    tr:nth-child(even) {
                        background-color: #f9f9f9;
                    }
                    .header {
                        text-align: center;
                        margin-bottom: 20px;
                    }
                    .header h1 {
                        font-size: 18px;
                        margin-bottom: 5px;
                    }
                    .header p {
                        font-size: 12px;
                        color: #666;
                    }
                    .charts-container {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 20px;
                    }
                    .chart {
                        width: 48%;
                    }
                    .chart h3 {
                        font-size: 14px;
                        margin-bottom: 10px;
                        text-align: center;
                    }
                    .chart img {
                        width: 100%;
                    }
                }
            </style>
        `;

        // Add header to print content
        let header = `
            <div class="header">
                <h1>Sit-in Records Report</h1>
                <p>Generated on: ${new Date().toLocaleString()}</p>
            </div>
        `;
        
        // Add charts to print content
        let chartsContent = `
            <div class="charts-container">
                <div class="chart">
                    <h3>Sit-ins per Lab</h3>
                    <img src="${labChartImg}">
                </div>
                <div class="chart">
                    <h3>Sit-ins per Reason</h3>
                    <img src="${reasonChartImg}">
                </div>
            </div>
            <h3 style="font-size: 14px; margin-bottom: 10px;">Detailed Sit-in Records</h3>
        `;

        document.body.innerHTML = printStyles + header + chartsContent + printContents;
        window.print();
        document.body.innerHTML = originalContents;
        
        // Reinitialize the charts and event listeners after printing
        location.reload();
    });

    // PDF Export functionality
    document.getElementById("exportPDF").addEventListener("click", function() {
        // Capture the charts as images first
        const labChartImg = document.getElementById('labChart').toDataURL('image/png');
        const reasonChartImg = document.getElementById('reasonChart').toDataURL('image/png');
        
        // Get the table element
        const table = document.getElementById("sitInTable");
        
        // Create a container for the PDF content
        const container = document.createElement('div');
        container.style.padding = '20px';
        
        // Add header
        const header = document.createElement('div');
        header.style.textAlign = 'center';
        header.style.marginBottom = '20px';
        header.innerHTML = `
            <h1 style="font-size: 18px; margin-bottom: 5px;">Sit-in Records Report</h1>
            <p style="font-size: 12px; color: #666;">Generated on: ${new Date().toLocaleString()}</p>
        `;
        
        // Create a div for charts
        const chartsDiv = document.createElement('div');
        chartsDiv.style.display = 'flex';
        chartsDiv.style.justifyContent = 'space-between';
        chartsDiv.style.marginBottom = '20px';
        
        // Add lab chart
        const labChartDiv = document.createElement('div');
        labChartDiv.style.width = '48%';
        labChartDiv.innerHTML = `
            <h3 style="font-size: 14px; margin-bottom: 10px; text-align: center;">Sit-ins per Lab</h3>
            <img src="${labChartImg}" style="width: 100%;">
        `;
        
        // Add reason chart
        const reasonChartDiv = document.createElement('div');
        reasonChartDiv.style.width = '48%';
        reasonChartDiv.innerHTML = `
            <h3 style="font-size: 14px; margin-bottom: 10px; text-align: center;">Sit-ins per Reason</h3>
            <img src="${reasonChartImg}" style="width: 100%;">
        `;
        
        // Append charts to charts div
        chartsDiv.appendChild(labChartDiv);
        chartsDiv.appendChild(reasonChartDiv);
        
        // Style the table
        table.style.width = '100%';
        table.style.borderCollapse = 'collapse';
        table.style.marginBottom = '20px';
        
        // Add styles to all cells
        const cells = table.querySelectorAll('th, td');
        cells.forEach(cell => {
            cell.style.border = '1px solid #ddd';
            cell.style.padding = '8px';
            cell.style.textAlign = 'left';
            cell.style.fontSize = '11px';
        });
        
        // Style header cells
        const headerCells = table.querySelectorAll('th');
        headerCells.forEach(cell => {
            cell.style.backgroundColor = '#f2f2f2';
            cell.style.fontWeight = 'bold';
            cell.style.color = '#333';
        });
        
        // Style even rows
        const rows = table.querySelectorAll('tr:nth-child(even)');
        rows.forEach(row => {
            row.style.backgroundColor = '#f9f9f9';
        });
        
        // Assemble the content
        container.appendChild(header);
        container.appendChild(chartsDiv);
        
        // Add a subheading for the table
        const tableHeading = document.createElement('h3');
        tableHeading.style.fontSize = '14px';
        tableHeading.style.marginBottom = '10px';
        tableHeading.style.marginTop = '20px';
        tableHeading.textContent = 'Detailed Sit-in Records';
        container.appendChild(tableHeading);
        
        container.appendChild(table.cloneNode(true));
        
        // Configuration for PDF
        const opt = {
            margin: 1,
            filename: 'sit_in_records.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { 
                scale: 2,
                logging: true,
                useCORS: true
            },
            jsPDF: { 
                unit: 'in', 
                format: 'letter', 
                orientation: 'landscape'
            }
        };

        // Generate PDF
        html2pdf().set(opt).from(container).save();
    });

    // Export to Excel
    document.getElementById("exportExcel").addEventListener("click", function() {
        let table = document.getElementById("sitInTable");
        let rows = table.querySelectorAll("tr");
        let excelData = [];

        // Add header row with current date
        let headers = ['Generated on: ' + new Date().toLocaleString()];
        excelData.push(headers);
        excelData.push([]); // Empty row for spacing
        
        // Add lab chart data
        excelData.push(['Sit-ins per Lab']);
        const labs = <?php echo json_encode($labs); ?>;
        const labCounts = <?php echo json_encode($labCounts); ?>;
        
        excelData.push(['Lab', 'Count']);
        for (let i = 0; i < labs.length; i++) {
            excelData.push([labs[i], labCounts[i]]);
        }
        
        excelData.push([]); // Empty row for spacing
        
        // Add reason chart data
        excelData.push(['Sit-ins per Reason']);
        const reasons = <?php echo json_encode($reasons); ?>;
        const reasonCounts = <?php echo json_encode($reasonCounts); ?>;
        
        excelData.push(['Reason', 'Count']);
        for (let i = 0; i < reasons.length; i++) {
            excelData.push([reasons[i], reasonCounts[i]]);
        }
        
        excelData.push([]); // Empty row for spacing
        excelData.push(['Detailed Sit-in Records']); // Table title
        excelData.push([]); // Empty row for spacing

        // Extract header row
        headers = [];
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

        // Set column widths
        const colWidths = [
            {wch: 25}, // Wider for the first column
            {wch: 25}, // Student Name/Count
            {wch: 20}, // Reason
            {wch: 15}, // Lab
            {wch: 15}, // In Time
            {wch: 15}, // Out Time
            {wch: 15}  // Date
        ];
        ws['!cols'] = colWidths;

        // Add some styling
        ws['!rows'] = [
            {hpt: 25}, // Header row height
            {hpt: 5},  // Spacing row height
            {hpt: 25}  // Column headers height
        ];

        XLSX.utils.book_append_sheet(wb, ws, "Sit-in Records");

        // Download Excel
        XLSX.writeFile(wb, "sit_in_records.xlsx");
    });

    // Leaderboard functionality
    function updateLeaderboard() {
        console.log('Starting leaderboard update...'); // Debug log
        
        const refreshBtn = document.getElementById('refreshLeaderboard');
        if (!refreshBtn) {
            console.error('Refresh button not found!');
            return;
        }
        refreshBtn.classList.add('spinning');
        
        console.log('Fetching leaderboard data...'); // Debug log
        fetch('leaderboard_fetch.php')
            .then(response => {
                console.log('Response received:', response.status); // Debug log
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Leaderboard data received:', data); // Debug log
                
                if (!Array.isArray(data)) {
                    console.error('Invalid data format:', data); // Debug log
                    throw new Error('Invalid data format received');
                }

                // Update podium
                const firstPlaceName = document.getElementById('firstPlaceName');
                const firstPlacePoints = document.getElementById('firstPlacePoints');
                const secondPlaceName = document.getElementById('secondPlaceName');
                const secondPlacePoints = document.getElementById('secondPlacePoints');
                const thirdPlaceName = document.getElementById('thirdPlaceName');
                const thirdPlacePoints = document.getElementById('thirdPlacePoints');
                
                if (!firstPlaceName || !firstPlacePoints || !secondPlaceName || 
                    !secondPlacePoints || !thirdPlaceName || !thirdPlacePoints) {
                    console.error('One or more podium elements not found!');
                    return;
                }

                // Update podium
                if (data.length >= 1) {
                    firstPlaceName.textContent = data[0].full_name;
                    firstPlacePoints.textContent = data[0].total_points + ' pts';
                } else {
                    firstPlaceName.textContent = 'No data';
                    firstPlacePoints.textContent = '0 pts';
                }
                
                if (data.length >= 2) {
                    secondPlaceName.textContent = data[1].full_name;
                    secondPlacePoints.textContent = data[1].total_points + ' pts';
                } else {
                    secondPlaceName.textContent = 'No data';
                    secondPlacePoints.textContent = '0 pts';
                }
                
                if (data.length >= 3) {
                    thirdPlaceName.textContent = data[2].full_name;
                    thirdPlacePoints.textContent = data[2].total_points + ' pts';
                } else {
                    thirdPlaceName.textContent = 'No data';
                    thirdPlacePoints.textContent = '0 pts';
                }

                // Update table
                const tableBody = document.getElementById('leaderboardTableBody');
                if (!tableBody) {
                    console.error('Leaderboard table body not found!');
                    return;
                }
                
                console.log('Updating table with', data.length, 'records'); // Debug log
                tableBody.innerHTML = '';
                
                if (data.length === 0) {
                    const row = document.createElement('tr');
                    row.innerHTML = `<td colspan="5" class="px-4 py-4 text-center text-gray-500">No leaderboard data available</td>`;
                    tableBody.appendChild(row);
                    return;
                }
                
                data.forEach((student, index) => {
                    const row = document.createElement('tr');
                    
                    // Add special styling for top 3
                    if (index < 3) {
                        row.className = 'bg-gray-50';
                    }
                    
                    row.innerHTML = `
                        <td class="px-4 py-3 text-sm text-gray-900">${index + 1}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">${student.idno}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">${student.full_name}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">${student.total_points}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">${student.total_sessions}</td>
                    `;
                    
                    tableBody.appendChild(row);
                });
                
                console.log('Leaderboard update completed successfully'); // Debug log
            })
            .catch(error => {
                console.error('Error fetching leaderboard data:', error);
                alert('Error loading leaderboard data. Please try again.');
                
                // Reset the podium and table to show error state
                const elements = {
                    firstPlaceName: document.getElementById('firstPlaceName'),
                    firstPlacePoints: document.getElementById('firstPlacePoints'),
                    secondPlaceName: document.getElementById('secondPlaceName'),
                    secondPlacePoints: document.getElementById('secondPlacePoints'),
                    thirdPlaceName: document.getElementById('thirdPlaceName'),
                    thirdPlacePoints: document.getElementById('thirdPlacePoints'),
                    tableBody: document.getElementById('leaderboardTableBody')
                };
                
                if (elements.firstPlaceName) elements.firstPlaceName.textContent = 'Error';
                if (elements.firstPlacePoints) elements.firstPlacePoints.textContent = '0 pts';
                if (elements.secondPlaceName) elements.secondPlaceName.textContent = 'Error';
                if (elements.secondPlacePoints) elements.secondPlacePoints.textContent = '0 pts';
                if (elements.thirdPlaceName) elements.thirdPlaceName.textContent = 'Error';
                if (elements.thirdPlacePoints) elements.thirdPlacePoints.textContent = '0 pts';
                
                if (elements.tableBody) {
                    elements.tableBody.innerHTML = '<tr><td colspan="5" class="px-4 py-4 text-center text-red-500">Error loading leaderboard data</td></tr>';
                }
            })
            .finally(() => {
                refreshBtn.classList.remove('spinning');
            });
    }

    // Initial leaderboard load
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, initializing leaderboard...'); // Debug log
        updateLeaderboard();
    });

    // Refresh leaderboard when button is clicked
    document.getElementById('refreshLeaderboard').addEventListener('click', function() {
        console.log('Refresh button clicked'); // Debug log
        this.classList.add('animate-spin');
        updateLeaderboard();
        setTimeout(() => this.classList.remove('animate-spin'), 1000);
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

