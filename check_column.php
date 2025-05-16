<?php
require_once 'config/config.php';

// Check if the last_used column exists in pcs table
$checkColumn = "SHOW COLUMNS FROM pcs LIKE 'last_used'";
$columnResult = $conn->query($checkColumn);

if ($columnResult->num_rows == 0) {
    // Column doesn't exist, add it
    echo "Adding last_used column to pcs table...<br>";
    $addColumn = "ALTER TABLE pcs ADD COLUMN last_used TIMESTAMP NULL DEFAULT NULL";
    if ($conn->query($addColumn)) {
        echo "last_used column added successfully!<br>";
    } else {
        echo "Error adding column: " . $conn->error . "<br>";
    }
} else {
    echo "last_used column already exists.<br>";
}

echo "Done.";
?>
