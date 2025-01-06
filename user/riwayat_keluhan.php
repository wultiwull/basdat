<?php
include "../includes/auth.php";
requireLogin();
include "../includes/config.php";

// Ambil data pengguna
$stmt = $pdo->prepare("SELECT nama, email FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Ambil data keluhan pengguna berdasarkan user_id
$stmt = $pdo->prepare("SELECT keluhan.id, keluhan.judul, keluhan.deskripsi, keluhan.status, kategori_keluhan.nama_kategori 
                    FROM keluhan 
                    JOIN kategori_keluhan ON keluhan.kategori_id = kategori_keluhan.id 
                    WHERE keluhan.user_id = :user_id 
                    ORDER BY keluhan.created_at DESC");
$stmt->execute(['user_id' => $_SESSION['user_id']]);
$keluhan = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Keluhan</title>
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
            <h2>Riwayat Keluhan</h2>
            <?php if (count($keluhan) > 0): ?>
                <table class="table table-bordered mt-4">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Kategori</th>
                            <th>Judul</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($keluhan as $k): ?>
                            <tr>
                                <td><?= $k['id'] ?></td>
                                <td><?= $k['nama_kategori'] ?></td>
                                <td><?= $k['judul'] ?></td>
                                <td><?= nl2br($k['deskripsi']) ?></td>
                                <td>
                                    <?php if ($k['status'] === 'pending'): ?>
                                        <span class="badge badge-warning">Pending</span>
                                    <?php elseif ($k['status'] === 'proses'): ?>
                                        <span class="badge badge-info">Proses</span>
                                    <?php elseif ($k['status'] === 'selesai'): ?>
                                        <span class="badge badge-success">Selesai</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="alert alert-info">Anda belum memiliki keluhan yang dilaporkan.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
