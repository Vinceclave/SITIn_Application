<?php
/**
 * Leaderboard Update Script
 * 
 * This script updates the leaderboard table with the latest data from sit_in and lab_points tables.
 * It can be run as a cron job to keep the leaderboard data up-to-date.
 * 
 * Example cron job (run every hour):
 * 0 * * * * php /path/to/update_leaderboard_cron.php
 */

// Include database connection
require_once '../config/config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log the start of the update process
error_log('Starting leaderboard update at ' . date('Y-m-d H:i:s'));

try {
    // Update the leaderboard table with current data
    $updateQuery = "INSERT INTO leaderboard (idno, full_name, total_sessions, total_points, last_updated)
                    SELECT 
                        s.idno,
                        s.full_name,
                        COUNT(DISTINCT s.sit_in_id) as total_sessions,
                        0 as total_points,
                        NOW() as last_updated
                    FROM sit_in s
                    GROUP BY s.idno, s.full_name
                    ON DUPLICATE KEY UPDATE
                        full_name = VALUES(full_name),
                        total_sessions = VALUES(total_sessions),
                        total_points = VALUES(total_points),
                        last_updated = VALUES(last_updated)";
    
    $stmt = $conn->prepare($updateQuery);
    $stmt->execute();
    
    // Log the number of rows affected
    $affectedRows = $stmt->rowCount();
    error_log("Leaderboard updated successfully. Affected rows: " . $affectedRows);
    
    // Output success message if run directly (not via cron)
    if (php_sapi_name() !== 'cli') {
        echo "Leaderboard updated successfully! Affected rows: " . $affectedRows;
    }
    
} catch (PDOException $e) {
    // Log the error
    error_log('Error updating leaderboard: ' . $e->getMessage());
    
    // Output error message if run directly (not via cron)
    if (php_sapi_name() !== 'cli') {
        echo "Error updating leaderboard: " . $e->getMessage();
    }
}
?> 