<?php
session_start();

// Check if user is logged in
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

require_once "../db_config.php";

// Get user's active bookings
$active_bookings_sql = "SELECT b.*, r.room_number, r.room_type, r.price_per_day 
                       FROM bookings b 
                       JOIN rooms r ON b.room_id = r.id 
                       WHERE b.user_id = ? AND b.check_out_date >= CURDATE() 
                       ORDER BY b.check_in_date ASC";

$active_bookings = [];
if($stmt = mysqli_prepare($conn, $active_bookings_sql)){
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        while($row = mysqli_fetch_assoc($result)){
            $active_bookings[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
}

// Get available rooms
$available_rooms_sql = "SELECT * FROM rooms WHERE id NOT IN 
                       (SELECT room_id FROM bookings 
                        WHERE check_out_date >= CURDATE())";
$available_rooms = [];
$result = mysqli_query($conn, $available_rooms_sql);
if($result){
    while($row = mysqli_fetch_assoc($result)){
        $available_rooms[] = $row;
    }
}

// Get unread notifications
$notifications_sql = "SELECT * FROM notifications 
                     WHERE user_id = ? AND is_read = FALSE 
                     ORDER BY created_at DESC";
$notifications = [];
if($stmt = mysqli_prepare($conn, $notifications_sql)){
    mysqli_stmt_bind_param($stmt, "i", $_SESSION["id"]);
    if(mysqli_stmt_execute($stmt)){
        $result = mysqli_stmt_get_result($stmt);
        while($row = mysqli_fetch_assoc($result)){
            $notifications[] = $row;
        }
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - DogeMate Rooms</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <nav class="nav nav-scrolled">
        <a href="../index.php" class="nav-brand">DogeMate</a>
        <div class="menu-toggle">
            <i class="fas fa-bars"></i>
        </div>
        <div class="nav-links">
            <a href="../rooms.php" class="nav-link">View Rooms</a>
            <a href="../booking_status.php" class="nav-link">My Bookings</a>
            <div class="notification-icon">
                <i class="fas fa-bell"></i>
                <?php if(count($notifications) > 0): ?>
                    <span class="notification-badge"><?php echo count($notifications); ?></span>
                <?php endif; ?>
            </div>
            <a href="../logout.php" class="nav-link">Logout</a>
        </div>
    </nav>

    <!-- Notifications Dropdown -->
    <div class="notifications-dropdown">
        <h3>Notifications</h3>
        <?php if(count($notifications) > 0): ?>
            <?php foreach($notifications as $notification): ?>
                <div class="notification-item">
                    <p><?php echo htmlspecialchars($notification['message']); ?></p>
                    <small><?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-notifications">No new notifications</p>
        <?php endif; ?>
    </div>

    <div class="dashboard-container">
        <h1 class="welcome-message">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <i class="fas fa-calendar-check"></i>
                <h3>Active Bookings</h3>
                <p><?php echo count($active_bookings); ?></p>
            </div>
            <div class="stat-card">
                <i class="fas fa-door-open"></i>
                <h3>Available Rooms</h3>
                <p><?php echo count($available_rooms); ?></p>
            </div>
        </div>

        <section class="dashboard-section">
            <h2>Your Active Bookings</h2>
            <?php if(empty($active_bookings)): ?>
                <p class="no-bookings">You have no active bookings.</p>
            <?php else: ?>
                <div class="booking-cards">
                    <?php foreach($active_bookings as $booking): ?>
                        <div class="booking-card">
                            <div class="booking-header">
                                <h3>Room <?php echo htmlspecialchars($booking['room_number']); ?></h3>
                                <span class="booking-status">Active</span>
                            </div>
                            <div class="booking-details">
                                <p><i class="fas fa-calendar-alt"></i> Check-in: <?php echo htmlspecialchars($booking['check_in_date']); ?></p>
                                <p><i class="fas fa-calendar-alt"></i> Check-out: <?php echo htmlspecialchars($booking['check_out_date']); ?></p>
                                <p><i class="fas fa-money-bill-wave"></i> Total: LKR <?php echo number_format($booking['total_price'], 2); ?></p>
                            </div>
                            <a href="../booking_details.php?id=<?php echo $booking['id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="dashboard-section">
            <h2>Available Rooms</h2>
            <?php if(empty($available_rooms)): ?>
                <p class="no-rooms">No rooms available at the moment.</p>
            <?php else: ?>
                <div class="room-cards">
                    <?php foreach($available_rooms as $room): ?>
                        <div class="room-card">
                            <img src="../assets/images/<?php echo strtolower(str_replace(' ', '-', $room['room_type'])); ?>.jpg" 
                                 alt="<?php echo htmlspecialchars($room['room_type']); ?>" 
                                 class="room-image">
                            <div class="room-content">
                                <h3>Room <?php echo htmlspecialchars($room['room_number']); ?></h3>
                                <div class="room-info">
                                    <p>Type: <?php echo htmlspecialchars($room['room_type']); ?></p>
                                    <p>Capacity: <?php echo htmlspecialchars($room['capacity']); ?> person(s)</p>
                                    <p>Price: <span class="price">LKR <?php echo number_format($room['price_per_day'], 2); ?>/night</span></p>
                                </div>
                                <a href="../book_room.php?id=<?php echo $room['id']; ?>" class="btn btn-primary">Book Now</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <footer class="footer">
        <div class="footer-grid">
            <div class="footer-links">
                <h4>Quick Links</h4>
                <a href="../rooms.php">Rooms</a>
                <a href="../booking_status.php">My Bookings</a>
                <a href="#">Contact</a>
            </div>
            <div class="footer-links">
                <h4>Contact Us</h4>
                <p><i class="fas fa-phone"></i> +94 11 234 5678</p>
                <p><i class="fas fa-mobile-alt"></i> +94 77 123 4567</p>
                <p><i class="fas fa-envelope"></i> info@dogematerooms.lk</p>
                <p><i class="fas fa-map-marker-alt"></i> 123 Galle Road, Colombo 03, Sri Lanka</p>
            </div>
            <div class="footer-links">
                <h4>Follow Us</h4>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 DogeMate Rooms. All rights reserved.</p>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
    <script>
    $(document).ready(function() {
        // Toggle notifications dropdown
        $('.notification-icon').click(function() {
            $('.notifications-dropdown').toggle();
        });

        // Close notifications when clicking outside
        $(document).click(function(e) {
            if (!$(e.target).closest('.notification-icon, .notifications-dropdown').length) {
                $('.notifications-dropdown').hide();
            }
        });

        // Mark notifications as read when viewed
        $('.notifications-dropdown').on('click', function() {
            $.ajax({
                url: 'mark_notifications_read.php',
                method: 'POST',
                success: function(response) {
                    $('.notification-badge').remove();
                }
            });
        });
    });
    </script>
</body>
</html> 