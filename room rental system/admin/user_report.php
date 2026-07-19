<?php
session_start();
require_once "../db_config.php";

// Check if user is logged in and is admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin"){
    header("location: ../login.php");
    exit;
}

// Fetch all users
$sql = "SELECT id, username, email, created_at, role FROM users ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Information Report - DogeMate Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #bbb;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #444;
            color: #eee;
        }
        .nav {
            padding: 10px;
            background-color: #f8f8f8;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .nav-link {
            margin-right: 15px;
            color: #333;
            text-decoration: none;
            font-weight: 600;
        }
        .nav-link:hover {
            text-decoration: underline;
            color: #000;
        }
        .btn-back {
            margin-top: 20px;
            display: inline-block;
            padding: 8px 16px;
            background-color: #777;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn-back:hover {
            background-color: #555;
        }
        .wrapper {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            color: #222;
        }
        h2 {
            margin-bottom: 10px;
            color: #222;
        }
    </style>
</head>
<body>
    <div class="nav">
        <a href="dashboard_admin.php" class="nav-link">Admin Dashboard</a>
        <a href="logout.php" class="nav-link">Logout</a>
    </div>

    <div class="wrapper">
        <h2>User Information Report</h2>
        <a href="dashboard_admin.php" class="btn-back">← Back to Dashboard</a>

        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Registered At</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($result) > 0): ?>
                    <?php while($user = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($user['role'])); ?></td>
                            <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5">No users found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php mysqli_close($conn); ?>
