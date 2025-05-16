<?php
// Fix MySQL issues related to PC Management
// This script runs the SQL scripts needed to fix the database issues
require_once 'config/config.php';

// Check if the user is authorized
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    header("Location: login.php");
    exit;
}

$sql_files = [
    'database/add_last_used_column.sql',
    'database/fix_pc_sitin_trigger.sql',
    'database/update_pc_id_after_sitin.sql'
];

$results = [];

foreach ($sql_files as $file) {
    if (file_exists($file)) {
        $sql = file_get_contents($file);
        
        // Split statements by delimiter
        $sql = preg_replace('/DELIMITER\s+\/\//', '', $sql);
        $sql = preg_replace('/DELIMITER\s+;/', '', $sql);
        
        // Replace // with ;
        $sql = str_replace('//', ';', $sql);
        
        // Split by semicolon
        $statements = explode(';', $sql);
        
        $file_success = true;
        $file_errors = [];
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                try {
                    $stmt_result = $conn->query($statement);
                    if (!$stmt_result && $conn->errno != 1060) { // 1060 is "Duplicate column"
                        $file_success = false;
                        $file_errors[] = "Error: {$conn->error}";
                    }
                } catch (Exception $e) {
                    // Ignore duplicate column errors
                    if (strpos($e->getMessage(), "Duplicate column") === false) {
                        $file_success = false;
                        $file_errors[] = "Exception: {$e->getMessage()}";
                    }
                }
            }
        }
        
        $results[$file] = [
            'success' => $file_success,
            'errors' => $file_errors
        ];
    } else {
        $results[$file] = [
            'success' => false,
            'errors' => ["File not found"]
        ];
    }
}

// Direct SQL fixes as a fallback
$direct_fixes = [
    // Add last_used column if not exists
    "SELECT COUNT(*) as col_exists FROM information_schema.columns 
     WHERE table_schema = DATABASE() AND table_name = 'pcs' AND column_name = 'last_used'",
    
    "ALTER TABLE `pcs` ADD COLUMN `last_used` TIMESTAMP NULL DEFAULT NULL AFTER `status`",
    
    // Create the stored procedure
    "DROP PROCEDURE IF EXISTS `update_sitin_pc_id`",
    
    "CREATE PROCEDURE `update_sitin_pc_id`(IN sitin_id INT, IN pc_number_val INT, IN lab_name_val VARCHAR(50))
     BEGIN
         DECLARE pc_id_var INT;
         
         SELECT p.pc_id INTO pc_id_var
         FROM pcs p
         JOIN labs l ON p.lab_id = l.lab_id
         WHERE l.lab_name = lab_name_val AND p.pc_number = pc_number_val
         LIMIT 1;
         
         IF pc_id_var IS NOT NULL THEN
             UPDATE sit_in SET pc_id = pc_id_var WHERE sit_in_id = sitin_id;
         END IF;
     END"
];

$direct_results = [];

// Execute first statement to check if column exists
$check_stmt = $conn->query($direct_fixes[0]);
$column_exists = false;
if ($check_stmt) {
    $row = $check_stmt->fetch_assoc();
    $column_exists = $row['col_exists'] > 0;
}

// Add the column if it doesn't exist
if (!$column_exists) {
    try {
        $add_result = $conn->query($direct_fixes[1]);
        $direct_results['add_last_used'] = $add_result !== false;
    } catch (Exception $e) {
        $direct_results['add_last_used'] = false;
    }
}

// Create the stored procedure
try {
    $conn->query($direct_fixes[2]);
    $proc_result = $conn->query($direct_fixes[3]);
    $direct_results['create_procedure'] = $proc_result !== false;
} catch (Exception $e) {
    $direct_results['create_procedure'] = false;
}

// Prepare result output
$all_success = true;
foreach ($results as $file => $result) {
    if (!$result['success']) {
        $all_success = false;
    }
}

// Output and redirect
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Fix Results</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css">
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-lg mx-auto bg-white shadow-md rounded-lg overflow-hidden">
            <div class="bg-indigo-600 text-white px-6 py-4">
                <h1 class="text-xl font-semibold">Database Fix Results</h1>
            </div>
            <div class="p-6">
                <?php if ($all_success): ?>
                <div class="mb-4 text-green-600 font-medium">
                    <p>All SQL scripts have been applied successfully.</p>
                </div>
                <?php else: ?>
                <div class="mb-4 text-yellow-600 font-medium">
                    <p>Some SQL operations reported errors but that might be normal (e.g., duplicate column).</p>
                </div>
                <?php endif; ?>
                
                <div class="mb-6">
                    <h2 class="text-lg font-medium text-gray-800 mb-2">Details:</h2>
                    <ul class="space-y-2">
                        <?php foreach ($results as $file => $result): ?>
                        <li class="text-sm">
                            <span class="font-medium"><?= basename($file) ?>:</span> 
                            <span class="<?= $result['success'] ? 'text-green-500' : 'text-yellow-500' ?>">
                                <?= $result['success'] ? 'Success' : 'Warning' ?>
                            </span>
                            
                            <?php if (!$result['success'] && !empty($result['errors'])): ?>
                            <ul class="ml-4 text-xs text-gray-600 mt-1">
                                <?php foreach ($result['errors'] as $error): ?>
                                <li>- <?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <div class="flex justify-center">
                    <a href="admin/pc_management.php" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                        Go to PC Management
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
