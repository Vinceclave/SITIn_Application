<?php
require_once 'config/config.php';

// First check if the column exists
$checkColumn = "SHOW COLUMNS FROM pcs LIKE 'last_used'";
$columnResult = $conn->query($checkColumn);

// If column doesn't exist, add it
if ($columnResult->num_rows == 0) {
    echo "Adding last_used column to pcs table...<br>";
    $addColumn = "ALTER TABLE pcs ADD COLUMN last_used TIMESTAMP NULL DEFAULT NULL";
    if ($conn->query($addColumn)) {
        echo "last_used column added successfully!<br>";
    } else {
        echo "Error adding column: " . $conn->error . "<br>";
        die("Failed to add column. Please fix the database structure first.");
    }
} else {
    echo "last_used column already exists.<br>";
}

// Update any potential problem areas for using this column
echo "<hr>Now fixing update_reservation_status.php...<br>";

// Check if the file using the last_used column is properly handling it
$updateReservationPath = 'admin/update_reservation_status.php';
if (file_exists($updateReservationPath)) {
    $fileContent = file_get_contents($updateReservationPath);
    
    // First check if the file already contains a safe last_used update statement
    if (strpos($fileContent, 'UPDATE pcs SET status = \'unavailable\', last_used = NOW()') !== false) {
        echo "update_reservation_status.php appears to be using last_used column correctly.<br>";
    } else {
        echo "Warning: update_reservation_status.php might need to be checked manually.<br>";
    }
}

// Fix the sitting_process.php file if it exists
$sittingProcessPath = 'admin/sitting_process.php';
if (file_exists($sittingProcessPath)) {
    $fileContent = file_get_contents($sittingProcessPath);
    
    // First check if the file already contains a safe last_used update statement
    if (strpos($fileContent, 'UPDATE pcs SET status = \'unavailable\', last_used = NOW()') !== false) {
        echo "sitting_process.php appears to be using last_used column correctly.<br>";
    } else {
        echo "Warning: sitting_process.php might need to be checked manually.<br>";
    }
}

echo "<hr>Database and code should now be compatible. If you still see errors, please check if any other files are using the last_used column.<br>";
?>
