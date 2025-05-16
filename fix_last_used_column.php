<?php
require_once 'config/config.php';

echo "<h2>Adding last_used column to pcs table</h2>";

// Try to add the column directly
// If it already exists, MySQL will show a warning but the script will continue
$addColumnSQL = "ALTER TABLE pcs ADD COLUMN last_used TIMESTAMP NULL DEFAULT NULL AFTER status";

try {
    // Execute the query - if it fails with a duplicate column error, that's okay
    if ($conn->query($addColumnSQL)) {
        echo "<p style='color:green'>Successfully added last_used column to pcs table.</p>";
    } else {
        // Check if the error is just that the column already exists (error 1060)
        if ($conn->errno == 1060) {
            echo "<p>The last_used column already exists in the pcs table.</p>";
        } else {
            echo "<p style='color:red'>Error: " . $conn->error . "</p>";
        }
    }
} catch (Exception $e) {
    // Check if the exception is just that the column already exists
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "<p>The last_used column already exists in the pcs table.</p>";
    } else {
        echo "<p style='color:red'>Exception: " . $e->getMessage() . "</p>";
    }
}

echo "<h3>Fix complete. You should no longer see 'Unknown column last_used' errors.</h3>";
echo "<p><a href='admin/manage_reservations.php'>Return to Manage Reservations</a></p>";
?>
