<?php
session_start();
require_once "db_config.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT id, room_number, room_type, capacity, price_per_day, status FROM rooms ORDER BY room_number";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Available Rooms - DogeMate Rooms</title>
    <style>
    /* Paste your entire CSS here */
    /* Classic Luxury Style */
    :root {
        --primary-color: #1a1a1a;
        --secondary-color: #8b7355;
        --accent-color: #c0a080;
        --text-color: #333;
        --light-bg: #f5f5f5;
        --white: #ffffff;
        --gold: #d4af37;
        --shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        --transition: all 0.3s ease;
        --heading-font: 'Playfair Display', serif;
        --body-font: 'Montserrat', sans-serif;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: var(--body-font);
        background-color: var(--light-bg);
        color: var(--text-color);
        line-height: 1.8;
        overflow-x: hidden;
        scroll-behavior: smooth;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }

    .nav {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        background: var(--white);
        padding: 10px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 60px;
        box-shadow: var(--shadow);
        z-index: 1000;
        border-bottom: 2px solid var(--secondary-color);
        transition: var(--transition);
    }

    .nav-link {
        color: var(--primary-color);
        text-decoration: none;
        padding: 10px 20px;
        border-radius: 0;
        transition: var(--transition);
        font-weight: 500;
        letter-spacing: 1px;
        position: relative;
        white-space: nowrap;
    }

    .nav-link:hover {
        color: var(--secondary-color);
    }

    .wrapper {
        max-width: 900px;
        margin: 80px auto 40px auto;
        background: #fff8f3;
        border-radius: 16px;
        box-shadow: 0 8px 32px rgba(139, 115, 85, 0.15), 0 1.5px 4px rgba(139, 115, 85, 0.08);
        padding: 2.5rem 2.5rem 2rem 2.5rem;
        border: 1.5px solid #8b7355;
        text-align: center;
        position: relative;
        z-index: 2;
    }

    .wrapper h2 {
        font-family: var(--heading-font);
        font-size: 2.2rem;
        color: #5a3c1a;
        margin-bottom: 1.5rem;
        letter-spacing: 1px;
    }

    .room-list {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 20px;
    }

    .room-card {
        background: var(--white);
        border: 1.5px solid var(--secondary-color);
        border-radius: 16px;
        width: 260px;
        box-shadow: var(--shadow);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: var(--transition);
    }

    .room-card:hover {
        box-shadow: 0 10px 20px rgba(212, 175, 55, 0.5);
        border-color: var(--gold);
        transform: translateY(-5px);
    }

    .card-content {
        padding: 1.5rem;
        text-align: left;
    }

    .card-content h3 {
        font-family: var(--heading-font);
        font-size: 1.6rem;
        margin: 0 0 0.7rem 0;
        color: var(--primary-color);
    }

    .room-info p {
        margin: 0.3rem 0;
        font-size: 1rem;
        color: var(--text-color);
    }

    .price {
        font-weight: 700;
        color: var(--secondary-color);
    }

    .badge {
        padding: 3px 10px;
        font-size: 0.9rem;
        border-radius: 12px;
        font-weight: 600;
        text-transform: capitalize;
        color: var(--white);
        display: inline-block;
        min-width: 90px;
        text-align: center;
    }

    .badge-success {
        background-color: #4CAF50;
    }

    .badge-warning {
        background-color: #FF9800;
    }

    .badge-danger {
        background-color: #f44336;
    }

    .card-actions {
        padding: 1rem 1.5rem 1.5rem 1.5rem;
        text-align: center;
    }

    .btn {
        padding: 10px 20px;
        font-size: 1rem;
        font-weight: 600;
        border-radius: 8px;
        cursor: pointer;
        border: none;
        transition: var(--transition);
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .btn-primary {
        background-color: var(--secondary-color);
        color: var(--white);
        box-shadow: var(--shadow);
    }

    .btn-primary:hover {
        background-color: var(--gold);
        color: var(--primary-color);
    }

    .btn-secondary {
        background-color: #c0a080;
        color: var(--primary-color);
        cursor: not-allowed;
    }

    .btn-secondary:hover {
        background-color: #a68c6a;
    }

    @media (max-width: 768px) {
        .room-list {
            flex-direction: column;
            align-items: center;
        }

        .room-card {
            width: 90%;
        }
    }
    </style>
</head>
<body>
    <nav class="nav">
        <a href="index.php" class="nav-link">Home</a>
        <a href="booking_status.php" class="nav-link">My Bookings</a>
        <?php if($_SESSION["role"] === "admin"): ?>
            <a href="admin/dashboard_admin.php" class="nav-link">Admin Dashboard</a>
        <?php endif; ?>
        <a href="logout.php" class="nav-link">Logout</a>
    </nav>

    <div class="wrapper">
        <h2>Available Rooms</h2>
        <div class="room-list">
            <?php
            if(mysqli_num_rows($result) > 0){
                while($room = mysqli_fetch_assoc($result)){
                    $statusClass = $room['status'] === 'available' ? 'badge-success' : 
                                ($room['status'] === 'maintenance' ? 'badge-danger' : 'badge-warning');
            ?>
                <div class="room-card">
                    <div class="card-content">
                        <h3>Room <?php echo htmlspecialchars($room['room_number']); ?></h3>
                        <div class="room-info">
                            <p>Type: <?php echo htmlspecialchars($room['room_type']); ?></p>
                            <p>Capacity: <?php echo htmlspecialchars($room['capacity']); ?> person(s)</p>
                            <p>Price: <span class="price">LKR <?php echo number_format($room['price_per_day'], 2); ?>/night</span></p>
                            <p>Status: <span class="badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($room['status']); ?></span></p>
                        </div>
                    </div>
                    <div class="card-actions">
                        <?php if($room['status'] === 'available'): ?>
                            <form action="book_room.php" method="get">
                                <input type="hidden" name="room_id" value="<?php echo (int)$room['id']; ?>">
                                <button type="submit" class="btn btn-primary">Book Now</button>
                            </form>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>Not Available</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php
                }
            } else {
                echo "<p>No rooms found.</p>";
            }
            mysqli_close($conn);
            ?>
        </div>
    </div>
</body>
</html>
