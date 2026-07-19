<?php
session_start();
require_once "../db_config.php";

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: ../login.php");
    exit;
}

$room_err = "";
$success_msg = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Validate room number
    if(empty(trim($_POST["room_number"]))){
        $room_err = "Please enter a room number.";
    } else {
        // Check if room number already exists
        $sql = "SELECT id FROM rooms WHERE room_number = ?";
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_room_number);
            $param_room_number = trim($_POST["room_number"]);
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                if(mysqli_stmt_num_rows($stmt) > 0){
                    $room_err = "This room number already exists.";
                }
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    // If no errors, proceed with insertion
    if(empty($room_err)){
        $sql = "INSERT INTO rooms (room_number, room_type, capacity, price_per_day, status) VALUES (?, ?, ?, ?, ?)";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "ssids", 
                $param_room_number,
                $param_room_type,
                $param_capacity,
                $param_price,
                $param_status
            );
            
            // Set parameters
            $param_room_number = trim($_POST["room_number"]);
            $param_room_type = trim($_POST["room_type"]);
            $param_capacity = (int)$_POST["capacity"];
            $param_price = (float)$_POST["price_per_day"];
            $param_status = $_POST["status"];
            
            if(mysqli_stmt_execute($stmt)){
                $success_msg = "Room added successfully!";
            } else{
                $room_err = "Something went wrong. Please try again later.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Room - DogeMate Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="nav">
        <a href="../index.php" class="nav-brand">DogeMate</a>
        <div class="nav-links">
            <a href="dashboard_admin.php" class="nav-link">Dashboard</a>
            <a href="../rooms.php" class="nav-link">View Rooms</a>
            <a href="../logout.php" class="nav-link">Logout</a>
        </div>
    </div>

    <div class="wrapper">
        <h2>Add New Room</h2>
        
        <?php 
        if(!empty($room_err)){
            echo '<div class="alert alert-danger">' . $room_err . '</div>';
        }
        if(!empty($success_msg)){
            echo '<div class="alert alert-success">' . $success_msg . '</div>';
        }
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Room Number</label>
                <input type="text" name="room_number" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Room Type</label>
                <select name="room_type" class="form-control" required>
                    <option value="Standard Single Room">Standard Single Room</option>
                    <option value="Standard Double Room">Standard Double Room</option>
                    <option value="Deluxe Single Room">Deluxe Single Room</option>
                    <option value="Deluxe Double Room">Deluxe Double Room</option>
                    <option value="Suite">Suite</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Capacity (persons)</label>
                <input type="number" name="capacity" class="form-control" min="1" max="6" required>
            </div>
            
            <div class="form-group">
                <label>Price per Night (LKR)</label>
                <input type="number" name="price_per_day" class="form-control" min="0" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label>Status</label>
                <select name="status" class="form-control" required>
                    <option value="available">Available</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="occupied">Occupied</option>
                </select>
            </div>
            
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Add Room">
                <a href="dashboard_admin.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
<?php mysqli_close($conn); ?> 