<?php
session_start();
require_once "db_config.php";

// Check if the user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Fetch user's bookings
$sql = "SELECT b.*, r.room_number, r.room_type, r.price_per_day 
        FROM bookings b 
        JOIN rooms r ON b.room_id = r.id 
        WHERE b.user_id = ? 
        ORDER BY b.created_at DESC";

if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Bookings - lodgeMate Rooms</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="nav">
        <a href="index.php" class="nav-link">Home</a>
        <a href="rooms.php" class="nav-link">View Rooms</a>
        <?php if($_SESSION["role"] === "admin"): ?>
            <a href="admin/dashboard_admin.php" class="nav-link">Admin Dashboard</a>
        <?php endif; ?>
        <a href="logout.php" class="nav-link">Logout</a>
    </div>

    <div class="wrapper">
        <h2>My Bookings</h2>
        <?php if(mysqli_num_rows($result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Type</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Total Price</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($booking = mysqli_fetch_assoc($result)): 
                        $statusClass = $booking['booking_status'] === 'confirmed' ? 'badge-success' : 
                                    ($booking['booking_status'] === 'cancelled' ? 'badge-danger' : 
                                    ($booking['booking_status'] === 'completed' ? 'badge-secondary' : 'badge-warning'));
                    ?>
                        <tr>
                            <td>Room <?php echo htmlspecialchars($booking['room_number']); ?></td>
                            <td><?php echo htmlspecialchars($booking['room_type']); ?></td>
                            <td><?php echo htmlspecialchars($booking['check_in_date']); ?></td>
                            <td><?php echo htmlspecialchars($booking['check_out_date']); ?></td>
                            <td>LKR <?php echo number_format($booking['total_price'], 2); ?></td>
                            <td><span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($booking['booking_status']); ?></span></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You haven't made any bookings yet.</p>
            <a href="rooms.php" class="btn btn-primary">Book a Room</a>
        <?php endif; 
        mysqli_close($conn);
        ?>
    </div>
</body>
</html> 