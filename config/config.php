<?php
// Database configuration
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'sitin_application');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

// Application settings
if (!defined('APP_DEBUG')) define('APP_DEBUG', true);
if (!defined('APP_URL')) define('APP_URL', 'http://localhost/SITIn_Application');

// Timezone setting
date_default_timezone_set('America/New_York');

// Database connection
if (!isset($servername)) $servername = "localhost";
if (!isset($username)) $username = "root";
if (!isset($password)) $password = "";
if (!isset($dbname)) $dbname = "sitin_application";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
