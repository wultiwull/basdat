<?php
include "../includes/auth.php";
requireLogin();
include "../includes/config.php";

// Ambil data pengguna
$stmt = $pdo->prepare("SELECT nama, email FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="bg-dark text-white p-3" style="width: 250px; min-height: 100vh;">
            <div class="text-center mb-4">
                <img src="../images/default-profile.png" alt="Profile Picture" class="rounded-circle" width="100" height="100">
                <h4 class="mt-2"><?= $user['nama'] ?></h4>
                <p><?= $user['email'] ?></p>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item"><a href="dashboard.php" class="nav-link text-white">Dashboard</a></li>
                <li class="nav-item"><a href="keluhan.php" class="nav-link text-white">Laporkan Keluhan</a></li>
                <li class="nav-item"><a href="riwayat_keluhan.php" class="nav-link text-white">Riwayat Keluhan</a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link text-white">Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="container mt-4">
            <h2>Dashboard User</h2>
            <!-- Tampilkan pesan sukses jika ada -->
            <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                <div class="alert alert-success">Keluhan berhasil dilaporkan!</div>
            <?php endif; ?>
            <div class="alert alert-info mt-4">
                <h4>Selamat datang, <?= $user['nama'] ?>!</h4>
                <p>Gunakan menu di sebelah kiri untuk melaporkan keluhan atau melihat riwayat keluhan Anda.</p>
            </div>
        </div>
    </div>
</body>
</html>
