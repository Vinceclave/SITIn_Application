<?php
// Database setup script for lab reservations

// Include configuration file
require_once 'config/config.php';

echo "Starting lab setup process...\n";

// Check if labs table exists
$checkLabsTableQuery = "SHOW TABLES LIKE 'labs'";
$checkLabsTableResult = $conn->query($checkLabsTableQuery);

if ($checkLabsTableResult->num_rows == 0) {
    echo "Labs table does not exist. Creating table...\n";
    
    // Create labs table
    $createLabsTableQuery = "CREATE TABLE labs (
        lab_id INT AUTO_INCREMENT PRIMARY KEY,
        lab_name VARCHAR(50) NOT NULL,
        total_pcs INT NOT NULL DEFAULT 50,
        location VARCHAR(100) NOT NULL,
        status ENUM('available', 'unavailable') DEFAULT 'available'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($createLabsTableQuery) === TRUE) {
        echo "Labs table created successfully.\n";
    } else {
        echo "Error creating labs table: " . $conn->error . "\n";
        exit;
    }
}

// The required lab data
$labsData = [
    ['524', 50, 'Main Building Floor 5'],
    ['526', 50, 'Main Building Floor 5'],
    ['528', 50, 'Main Building Floor 5'],
    ['530', 50, 'Main Building Floor 5'],
    ['542', 50, 'Main Building Floor 5'],
    ['544', 50, 'Main Building Floor 5'],
    ['517', 50, 'Main Building Floor 5']
];

// Check existing labs
$existingLabsQuery = "SELECT lab_name FROM labs";
$existingLabsResult = $conn->query($existingLabsQuery);
$existingLabs = [];

if ($existingLabsResult->num_rows > 0) {
    while ($row = $existingLabsResult->fetch_assoc()) {
        $existingLabs[] = $row['lab_name'];
    }
}

echo "Found " . count($existingLabs) . " existing lab records.\n";

// Prepare statement for inserting new labs
$insertStmt = $conn->prepare("INSERT INTO labs (lab_name, total_pcs, location, status) VALUES (?, ?, ?, 'available')");
$insertStmt->bind_param("sis", $lab_name, $total_pcs, $location);

$insertedCount = 0;

// Insert only labs that don't already exist
foreach ($labsData as $lab) {
    $lab_name = $lab[0];
    $total_pcs = $lab[1];
    $location = $lab[2];
    
    if (!in_array($lab_name, $existingLabs)) {
        $insertStmt->execute();
        $insertedCount++;
        echo "Added lab: $lab_name\n";
    } else {
        echo "Lab $lab_name already exists, skipping.\n";
    }
}

echo "Completed. Added $insertedCount new lab records.\n";

// Check if reservations table exists
$checkReservationsTableQuery = "SHOW TABLES LIKE 'reservations'";
$checkReservationsTableResult = $conn->query($checkReservationsTableQuery);

if ($checkReservationsTableResult->num_rows == 0) {
    echo "Reservations table does not exist. Creating table...\n";
    
    // Create reservations table
    $createReservationsTableQuery = "CREATE TABLE reservations (
        reservation_id INT AUTO_INCREMENT PRIMARY KEY,
        idno INT NOT NULL,
        full_name VARCHAR(255) NOT NULL,
        lab_name VARCHAR(50) NOT NULL,
        pc_number INT NOT NULL,
        reservation_date DATE NOT NULL,
        time_slot VARCHAR(50) NOT NULL,
        status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (idno) REFERENCES users(idno) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    
    if ($conn->query($createReservationsTableQuery) === TRUE) {
        echo "Reservations table created successfully.\n";
        
        // Add indexes for better performance
        $conn->query("CREATE INDEX idx_reservations_lab_date_slot ON reservations (lab_name, reservation_date, time_slot, status)");
        $conn->query("CREATE INDEX idx_reservations_user ON reservations (idno, reservation_date, status)");
        echo "Indexes for reservations table created successfully.\n";
    } else {
        echo "Error creating reservations table: " . $conn->error . "\n";
    }
}

$conn->close();
echo "Setup complete.\n";
?> 