<?php
session_start();
include '../includes/conn.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in and is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}

try {
    // Query to get student leaderboard data directly from sit_in and lab_points tables
    $query = "SELECT 
                s.idno,
                s.full_name,
                COUNT(DISTINCT s.sit_in_id) as total_sessions,
                COALESCE(SUM(lp.points), 0) as total_points
              FROM sit_in s
              LEFT JOIN lab_points lp ON s.sit_in_id = lp.sit_in_id
              GROUP BY s.idno, s.full_name
              ORDER BY total_points DESC, total_sessions DESC
              LIMIT 10";

    $stmt = $conn->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Display the results
    echo "<h1>Leaderboard Test Results</h1>";
    echo "<pre>";
    print_r($results);
    echo "</pre>";
    
    // Display raw data from tables
    echo "<h2>Raw Data from sit_in table</h2>";
    $stmt = $conn->prepare("SELECT * FROM sit_in LIMIT 5");
    $stmt->execute();
    $sitInData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($sitInData);
    echo "</pre>";
    
    echo "<h2>Raw Data from lab_points table</h2>";
    $stmt = $conn->prepare("SELECT * FROM lab_points LIMIT 5");
    $stmt->execute();
    $labPointsData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($labPointsData);
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 