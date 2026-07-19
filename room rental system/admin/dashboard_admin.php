<?php
session_start();
require_once "../db_config.php";

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: ../login.php");
    exit;
}

// Handle room status updates
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["room_id"]) && isset($_POST["status"])){
    $sql = "UPDATE rooms SET status = ? WHERE id = ?";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "si", $_POST["status"], $_POST["room_id"]);
        mysqli_stmt_execute($stmt);
    }
}

// Handle booking status updates
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["booking_id"]) && isset($_POST["booking_status"])){
    $booking_id = $_POST["booking_id"];
    $new_status = $_POST["booking_status"];
    
    // Update booking status
    $sql = "UPDATE bookings SET booking_status = ? WHERE id = ?";
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "si", $new_status, $booking_id);
        mysqli_stmt_execute($stmt);
        
        // If status is confirmed, create notification
        if($new_status === 'confirmed'){
            // Get booking details
            $sql = "SELECT user_id, room_id FROM bookings WHERE id = ?";
            if($stmt = mysqli_prepare($conn, $sql)){
                mysqli_stmt_bind_param($stmt, "i", $booking_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $booking = mysqli_fetch_assoc($result);
                
                // Get room details
                $sql = "SELECT room_number FROM rooms WHERE id = ?";
                if($stmt = mysqli_prepare($conn, $sql)){
                    mysqli_stmt_bind_param($stmt, "i", $booking['room_id']);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $room = mysqli_fetch_assoc($result);
                    
                    // Create notification message
                    $message = "Your booking for Room " . $room['room_number'] . " has been confirmed!";
                    
                    // Insert notification
                    $sql = "INSERT INTO notifications (user_id, booking_id, message) VALUES (?, ?, ?)";
                    if($stmt = mysqli_prepare($conn, $sql)){
                        mysqli_stmt_bind_param($stmt, "iis", $booking['user_id'], $booking_id, $message);
                        mysqli_stmt_execute($stmt);
                    }
                }
            }
        }
    }
}

// Fetch all rooms
$sql = "SELECT * FROM rooms ORDER BY room_number";
$rooms_result = mysqli_query($conn, $sql);

// Fetch recent bookings
$sql = "SELECT b.*, u.username, r.room_number 
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN rooms r ON b.room_id = r.id 
        ORDER BY b.created_at DESC 
        LIMIT 10";
$bookings_result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - DogeMate Rooms</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            font-weight: 600;
            transition: background-color 0.3s ease;
            margin-bottom: 20px;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            color: white;
        }
        .action-buttons {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="nav">
        <a href="../index.php" class="nav-brand">DogeMate</a>
        <a href="../rooms.php" class="nav-link">View Rooms</a>
        <a href="../logout.php" class="nav-link">Logout</a>
    </div>

    <div class="wrapper">
        <h2>Admin Dashboard</h2>

        <div class="action-buttons">
            <a href="user_report.php" class="btn-secondary">User Information Report</a>
        </div>

        <h3>Manage Rooms</h3>
        <div class="action-buttons">
            <a href="add_room.php" class="btn btn-primary">Add New Room</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Room Number</th>
                    <th>Type</th>
                    <th>Capacity</th>
                    <th>Price/Night</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($room = mysqli_fetch_assoc($rooms_result)): ?>
                    <tr>
                        <td>Room <?php echo htmlspecialchars($room['room_number']); ?></td>
                        <td><?php echo htmlspecialchars($room['room_type']); ?></td>
                        <td><?php echo htmlspecialchars($room['capacity']); ?></td>
                        <td>LKR <?php echo number_format($room['price_per_day'], 2); ?></td>
                        <td>
                            <span class="badge <?php 
                                echo $room['status'] === 'available' ? 'badge-success' : 
                                    ($room['status'] === 'maintenance' ? 'badge-danger' : 'badge-warning'); 
                            ?>">
                                <?php echo htmlspecialchars($room['status']); ?>
                            </span>
                        </td>
                        <td>
                            <form method="post" class="status-form">
                                <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                                <select name="status" onchange="this.form.submit()">
                                    <option value="available" <?php echo $room['status'] === 'available' ? 'selected' : ''; ?>>Available</option>
                                    <option value="occupied" <?php echo $room['status'] === 'occupied' ? 'selected' : ''; ?>>Occupied</option>
                                    <option value="maintenance" <?php echo $room['status'] === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <h3>Recent Bookings</h3>
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Room</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($booking = mysqli_fetch_assoc($bookings_result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($booking['username']); ?></td>
                        <td>Room <?php echo htmlspecialchars($booking['room_number']); ?></td>
                        <td><?php echo htmlspecialchars($booking['check_in_date']); ?></td>
                        <td><?php echo htmlspecialchars($booking['check_out_date']); ?></td>
                        <td>LKR <?php echo number_format($booking['total_price'], 2); ?></td>
                        <td>
                            <span class="badge <?php 
                                echo $booking['booking_status'] === 'confirmed' ? 'badge-success' : 
                                    ($booking['booking_status'] === 'cancelled' ? 'badge-danger' : 
                                    ($booking['booking_status'] === 'completed' ? 'badge-secondary' : 'badge-warning')); 
                            ?>">
                                <?php echo htmlspecialchars($booking['booking_status']); ?>
                            </span>
                        </td>
                        <td>
                            <form method="post" class="status-form">
                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                <select name="booking_status" onchange="this.form.submit()">
                                    <option value="pending" <?php echo $booking['booking_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="confirmed" <?php echo $booking['booking_status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="cancelled" <?php echo $booking['booking_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    <option value="completed" <?php echo $booking['booking_status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script>
    // Show success message when booking is confirmed
    $(document).ready(function() {
        $('.status-form').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            $.ajax({
                type: 'POST',
                url: '',
                data: form.serialize(),
                success: function(response) {
                    if(form.find('select[name="booking_status"]').val() === 'confirmed') {
                        alert('Booking confirmed! User has been notified.');
                    }
                    location.reload();
                }
            });
        });
    });
    </script>
</body>
</html>
<?php mysqli_close($conn); ?>
