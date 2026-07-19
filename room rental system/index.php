<?php
session_start();
require_once "db_config.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LodgeMate - Welcome</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="nav">
        <a href="index.php" class="nav-brand">LodgeMate</a>
        <div class="nav-links">
            <a href="rooms.php" class="nav-link">Browse Rooms</a>
            <a href="login.php" class="nav-link">Login</a>
            <a href="signup.php" class="nav-link">Register</a>
        </div>
    </div>

    <div class="hero">
        <h1>Experience Luxury at LodgeMate</h1>
        <p>Discover our collection of elegant rooms with breathtaking views</p>
        <a href="rooms.php" class="btn btn-primary">Browse Rooms</a>
        
        <div class="features">
            <div class="feature-item">
                <i>🌅</i>
                <h3>Scenic Views</h3>
                <p>Rooms with panoramic nature views</p>
            </div>
            <div class="feature-item">
                <i>🛋️</i>
                <h3>Modern Comfort</h3>
                <p>Elegant furniture and premium amenities</p>
            </div>
            <div class="feature-item">
                <i>✨</i>
                <h3>Premium Service</h3>
                <p>24/7 concierge at your service</p>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date("Y"); ?> LodgeMate. All rights reserved.</p>
    </footer>
</body>
</html>
