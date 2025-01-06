<?php
include "../includes/auth.php";
requireAdmin();
include "../includes/config.php";

// Fetch admin details
$stmt = $pdo->prepare("SELECT nama, email FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch statistics
$pending_count = $pdo->query("SELECT COUNT(*) FROM keluhan WHERE status = 'pending'")->fetchColumn();
$process_count = $pdo->query("SELECT COUNT(*) FROM keluhan WHERE status = 'proses'")->fetchColumn();
$completed_count = $pdo->query("SELECT COUNT(*) FROM keluhan WHERE status = 'selesai'")->fetchColumn();

// Fetch complaints
$keluhan = $pdo->query("SELECT * FROM keluhan")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="bg-dark text-white p-3" style="width: 250px; min-height: 100vh;">
            <div class="text-center mb-4">
                <img src="../images/default-profile.png" alt="Profile Picture" class="rounded-circle" width="100" height="100">
                <h4 class="mt-2"><?= htmlspecialchars($admin['nama']) ?></h4>
                <p><?= htmlspecialchars($admin['email']) ?></p>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item"><a href="dashboardAdm.php" class="nav-link text-white">Detail Keluhan</a></li>
                <li class="nav-item"><a href="kelola_keluhan.php" class="nav-link text-white">Kelola Keluhan</a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link text-white">Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="container mt-4">
            <h2>Dashboard Admin</h2>
            <div class="row">
                <div class="col-md-4">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Pending Keluhan</h5>
                            <p class="card-text"><?= $pending_count ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-warning mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Proses Keluhan</h5>
                            <p class="card-text"><?= $process_count ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Selesai Keluhan</h5>
                            <p class="card-text"><?= $completed_count ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <h3>Daftar Keluhan</h3>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Judul</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($keluhan)): ?>
                        <tr>
                            <td colspan="5" class="text-center">Tidak ada keluhan yang tersedia.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($keluhan as $k): ?>
                        <tr>
                            <td><?= htmlspecialchars($k['id']) ?></td>
                            <td><?= htmlspecialchars($k['judul']) ?></td>
                            <td><?= nl2br(htmlspecialchars($k['deskripsi'])) ?></td>
                            <td><?= ucfirst(htmlspecialchars($k['status'])) ?></td>
                            <td>
                                <!-- Tombol Edit -->
                                <a href="kelola_keluhan.php?id=<?= htmlspecialchars($k['id']) ?>" class="btn btn-warning btn-sm">Edit</a>
                                <!-- Tombol Hapus -->
                                <a href="delete_keluhan.php?id=<?= htmlspecialchars($k['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus keluhan ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
