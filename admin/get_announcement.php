<?php
require_once '../config/config.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    $query = "SELECT * FROM announcements WHERE announce_id = '$id'";
    $result = mysqli_query($conn, $query);
    $announcement = mysqli_fetch_assoc($result);
    echo json_encode($announcement);
}
