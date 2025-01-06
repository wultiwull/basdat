<?php
// Include konfigurasi dan autentikasi
include "../includes/config.php";
include "../includes/auth.php";
requireAdmin(); // Pastikan hanya admin yang dapat mengakses halaman ini

// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validasi session user_id
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] <= 0) {
    die("<div class='alert alert-danger'>Error: Anda harus login sebagai admin untuk mengakses halaman ini.</div>");
}

// Ambil data admin dari session untuk menampilkan informasi
$stmt = $pdo->prepare("SELECT nama, email FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Validasi jika admin tidak ditemukan
if (!$admin) {
    die("<div class='alert alert-danger'>Error: Admin tidak ditemukan. Silakan login kembali.</div>");
}

// Proses POST Simpan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['response_id'])) {
    $response_id = htmlspecialchars($_POST['response_id']);
    $status = htmlspecialchars($_POST['status']);
    $tanggapan = htmlspecialchars($_POST['tanggapan']);

    try {
        // Validasi ID keluhan
        $keluhanCheck = $pdo->prepare("SELECT id FROM keluhan WHERE id = :id");
        $keluhanCheck->execute(['id' => $response_id]);
        if ($keluhanCheck->rowCount() === 0) {
            die("<div class='alert alert-danger'>Error: ID keluhan tidak valid.</div>");
        }

        // Update data keluhan
        $updateStmt = $pdo->prepare("
            UPDATE keluhan 
            SET status = :status, 
                deskripsi = CONCAT(deskripsi, '\n\nTanggapan Admin: ', :tanggapan), 
                updated_at = NOW()
            WHERE id = :id
        ");
        $updateStmt->execute([
            'status' => $status,
            'tanggapan' => $tanggapan,
            'id' => $response_id,
        ]);

        // Tambahkan ke tabel log_keluhan
        $logStmt = $pdo->prepare("
            INSERT INTO log_keluhan (keluhan_id, perubahan, user_id, waktu_perubahan)
            VALUES (:keluhan_id, :perubahan, :user_id, NOW())
        ");
        $logStmt->execute([
            'keluhan_id' => $response_id,
            'perubahan' => "Status diubah menjadi '$status' dengan tanggapan: $tanggapan",
            'user_id' => $_SESSION['user_id'],
        ]);

        // Redirect setelah berhasil
        header("Location: kelola_keluhan.php");
        exit();
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}

// Ambil data keluhan untuk ditampilkan
$keluhan = $pdo->query("SELECT * FROM keluhan")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Kelola Keluhan</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
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
                <li class="nav-item"><a href="profile.php" class="nav-link text-white">Profile</a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link text-white">Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="container mt-5">
            <h1>Kelola Keluhan</h1>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Judul</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th>Tanggapan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($keluhan)): ?>
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada keluhan yang tersedia.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($keluhan as $k): ?>
                        <tr>
                            <td><?= htmlspecialchars($k['id']) ?></td>
                            <td><?= htmlspecialchars($k['judul']) ?></td>
                            <td><?= nl2br(htmlspecialchars($k['deskripsi'])) ?></td>
                            <td><?= ucfirst(htmlspecialchars($k['status'])) ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="response_id" value="<?= htmlspecialchars($k['id']) ?>">
                                    <select name="status" class="form-control mb-2" required>
                                        <option value="pending" <?= $k['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="proses" <?= $k['status'] === 'proses' ? 'selected' : '' ?>>Proses</option>
                                        <option value="selesai" <?= $k['status'] === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                                    </select>
                                    <textarea name="tanggapan" class="form-control mb-2" placeholder="Berikan tanggapan..." required></textarea>
                                    <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="delete_keluhan.php">
                                    <input type="hidden" name="delete_id" value="<?= htmlspecialchars($k['id']) ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
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
