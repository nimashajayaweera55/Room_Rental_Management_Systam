<?php
session_start();
require_once "db_config.php";

// Debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Check if room_id is provided
if(!isset($_GET["room_id"])){
    die("Room ID is missing. Please go back and try again.");
}

$room_id = $_GET["room_id"];
$booking_err = "";

// Debug output
echo "<!-- Debug: Room ID = " . htmlspecialchars($room_id) . " -->";

// Fetch room details
$sql = "SELECT * FROM rooms WHERE id = ? AND status = 'available'";
if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $room_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if(mysqli_num_rows($result) == 0){
        header("location: rooms.php");
        exit;
    }
    $room = mysqli_fetch_assoc($result);
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $check_in = trim($_POST["check_in"]);
    $check_out = trim($_POST["check_out"]);
    
    // Validate dates
    $today = date("Y-m-d");
    $check_in_date = new DateTime($check_in);
    $check_out_date = new DateTime($check_out);
    $today_date = new DateTime($today);
    
    if($check_in_date < $today_date){
        $booking_err = "Check-in date cannot be in the past.";
    } elseif($check_out_date <= $check_in_date){
        $booking_err = "Check-out date must be after check-in date.";
    } else {
        // Calculate total price
        $interval = $check_in_date->diff($check_out_date);
        $days = $interval->days;
        $total_price = $days * $room["price_per_day"];
        
        // Check if room is available for these dates
        $sql = "SELECT id FROM bookings WHERE room_id = ? AND booking_status IN ('confirmed', 'pending') 
                AND ((check_in_date <= ? AND check_out_date >= ?) 
                OR (check_in_date <= ? AND check_out_date >= ?) 
                OR (check_in_date >= ? AND check_out_date <= ?))";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "issssss", $room_id, $check_out, $check_out, $check_in, $check_in, $check_in, $check_out);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            
            if(mysqli_stmt_num_rows($stmt) > 0){
                $booking_err = "Room is not available for selected dates.";
            } else {
                // Create booking
                $sql = "INSERT INTO bookings (user_id, room_id, check_in_date, check_out_date, total_price) VALUES (?, ?, ?, ?, ?)";
                
                if($stmt = mysqli_prepare($conn, $sql)){
                    mysqli_stmt_bind_param($stmt, "iissd", $_SESSION["id"], $room_id, $check_in, $check_out, $total_price);
                    
                    if(mysqli_stmt_execute($stmt)){
                        header("location: booking_status.php");
                        exit();
                    } else{
                        $booking_err = "Something went wrong. Please try again later.";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Room - Room Rental System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="nav">
        <a href="index.php" class="nav-link">Home</a>
        <a href="rooms.php" class="nav-link">View Rooms</a>
        <a href="booking_status.php" class="nav-link">My Bookings</a>
        <a href="logout.php" class="nav-link">Logout</a>
    </div>

    <div class="wrapper">
        <h2>Book Room <?php echo htmlspecialchars($room["room_number"]); ?></h2>
        <div class="room-info">
            <p>Type: <?php echo htmlspecialchars($room["room_type"]); ?></p>
            <p>Capacity: <?php echo htmlspecialchars($room["capacity"]); ?> person(s)</p>
            <p>Price: <span class="price">LKR <?php echo number_format($room["price_per_day"], 2); ?>/night</span></p>
        </div>

        <?php 
        if(!empty($booking_err)){
            echo '<div class="alert alert-danger">' . $booking_err . '</div>';
        }        
        ?>

        <form action="book_room.php?room_id=<?php echo htmlspecialchars($room_id); ?>" method="post">
            <div class="form-group">
                <label>Check-in Date</label>
                <input type="date" name="check_in" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
            </div>    
            <div class="form-group">
                <label>Check-out Date</label>
                <input type="date" name="check_out" class="form-control" required min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
            </div>
            <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($room_id); ?>">
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Book Now">
                <a href="rooms.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html> 