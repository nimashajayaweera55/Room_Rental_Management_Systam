<?php
session_start();
require_once "../db_config.php";

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

$user_id = $_SESSION["id"];

// Mark all notifications as read for the current user
$sql = "UPDATE notifications SET is_read = TRUE WHERE user_id = ?";
if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
header("location: dashboard_user.php");
exit;
?> 