<?php
// Fix PC Management MySQL Errors
require_once '../config/config.php';

// Get list of SQL files to execute
$sql_files = [
    '../database/add_last_used_column.sql',
    '../database/fix_pc_sitin_trigger.sql',
    '../database/update_pc_id_after_sitin.sql'
];

// Function to run SQL file
function runSQLFile($conn, $filename) {
    $success = true;
    $errors = [];
    
    if (file_exists($filename)) {
        $sql = file_get_contents($filename);
        
        // Split SQL statements by delimiter
        $sql = preg_replace('/DELIMITER\s+\/\//', '', $sql);
        $sql = preg_replace('/DELIMITER\s+;/', '', $sql);
        
        // Replace // with ;
        $sql = str_replace('//', ';', $sql);
        
        // Split by semicolon
        $statements = explode(';', $sql);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                try {
                    if (!$conn->query($statement)) {
                        $success = false;
                        $errors[] = "Error executing statement: " . $conn->error;
                    }
                } catch (Exception $e) {
                    // Some errors are expected (like duplicate column)
                    // Only log critical errors
                    if (strpos($e->getMessage(), "Duplicate column") === false) {
                        $success = false;
                        $errors[] = "Exception: " . $e->getMessage();
                    }
                }
            }
        }
    } else {
        $success = false;
        $errors[] = "File not found: " . $filename;
    }
    
    return [
        'success' => $success,
        'errors' => $errors
    ];
}

// Execute each SQL file
$results = [];
foreach ($sql_files as $file) {
    $results[$file] = runSQLFile($conn, $file);
}

// Verify if last_used column exists
$check_column = $conn->query("SHOW COLUMNS FROM `pcs` LIKE 'last_used'");
$last_used_exists = $check_column->num_rows > 0;

if (!$last_used_exists) {
    // Try the direct approach if the SQL file didn't work
    $conn->query("ALTER TABLE `pcs` ADD COLUMN `last_used` TIMESTAMP NULL DEFAULT NULL AFTER `status`");
}

// Check if the stored procedure exists
$check_proc = $conn->query("SHOW PROCEDURE STATUS WHERE Db = DATABASE() AND Name = 'update_sitin_pc_id'");
$proc_exists = $check_proc->num_rows > 0;

// Output results for debugging (comment out in production)
/*
echo "<pre>";
print_r($results);
echo "\nLast Used Column Exists: " . ($last_used_exists ? "Yes" : "No");
echo "\nUpdate Procedure Exists: " . ($proc_exists ? "Yes" : "No");
echo "</pre>";
*/

// Return to the PC management page
header("Location: pc_management.php");
exit;
