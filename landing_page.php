<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keluhan Kampus</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #333;
            padding: 10px 20px;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 10px;
        }
        .navbar a:hover {
            background-color: #575757;
            border-radius: 5px;
        }
        .hero {
            text-align: center;
            padding: 100px 20px;
            background-color: #0099cc;
            color: white;
        }
        .hero h1 {
            font-size: 2.5em;
        }
        .hero p {
            font-size: 1.2em;
        }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #333;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn:hover {
            background-color: #575757;
        }
    </style>
</head>
<body>
    <!-- <div class="navbar">
        <div>
            <a href="index.php">Home</a>
            <a href="login.php">Login</a>
            <a href="registrasi.php">Register</a>
        </div> -->
        <div>
            <!-- Admin/User specific links will appear here based on session -->
            <?php
                session_start();
                if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                    echo '<a href="admin/dashboardAdm.php">Dashboard Admin</a>';
                } elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
                    echo '<a href="user/dashboard.php">Dashboard User</a>';
                }
            ?>
        </div>
    </div>
    <div class="hero">
        <h1>Welcome to Keluhan Kampus</h1>
        <p>Your one-stop solution for managing campus complaints efficiently.</p>
        <a class="btn" href="login.php">Get Started</a>
    </div>
</body>
</html>
