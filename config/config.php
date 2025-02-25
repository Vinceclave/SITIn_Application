<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'sitin_application');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application settings
define('APP_DEBUG', true);
define('APP_URL', 'http://localhost/SITIn_Application');

// Timezone setting
date_default_timezone_set('America/New_York');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sitin_application";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
