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

var_dump($_SESSION['user_id']);
exit();

$userCheck = $pdo->prepare("SELECT id FROM users WHERE id = :id");
$userCheck->execute(['id' => $_SESSION['user_id']]);
if ($userCheck->rowCount() === 0) {
    die("<div class='alert alert-danger'>Error: User ID tidak valid atau tidak ditemukan.</div>");
}

$keluhanCheck = $pdo->prepare("SELECT id FROM keluhan WHERE id = :id");
$keluhanCheck->execute(['id' => $response_id]);
if ($keluhanCheck->rowCount() === 0) {
    die("<div class='alert alert-danger'>Error: ID keluhan tidak valid.</div>");
}

// Ambil data admin dari session untuk menampilkan informasi
$stmt = $pdo->prepare("SELECT nama, email FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Validasi jika admin tidak ditemukan
if (!$admin) {
    die("<div class='alert alert-danger'>Error: Admin tidak ditemukan. Silakan login kembali.</div>");
}

$logStmt = $pdo->prepare("
    INSERT INTO log_keluhan (keluhan_id, perubahan, user_id, waktu_perubahan)
    VALUES (:keluhan_id, :perubahan, :user_id, NOW())
");
$logStmt->execute([
    'keluhan_id' => $response_id,
    'perubahan' => "Status diubah menjadi '$status' dengan tanggapan: $tanggapan",
    'user_id' => $_SESSION['user_id'],
]);

// Tambahkan log perubahan jika ada request POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['response_id'])) {
    $response_id = htmlspecialchars($_POST['response_id']);
    $status = htmlspecialchars($_POST['status']);
    $tanggapan = htmlspecialchars($_POST['tanggapan']);

    try {
        // Validasi response_id
        $keluhanCheck = $pdo->prepare("SELECT id FROM keluhan WHERE id = :id");
        $keluhanCheck->execute(['id' => $response_id]);
        if ($keluhanCheck->rowCount() === 0) {
            die("<div class='alert alert-danger'>Error: ID keluhan tidak valid.</div>");
        }

        // Update status dan tambahkan tanggapan
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

        // Validasi jika user_id valid di tabel users
        $adminCheck = $pdo->prepare("SELECT id FROM users WHERE id = :id");
        $adminCheck->execute(['id' => $_SESSION['user_id']]);
        if ($adminCheck->rowCount() === 0) {
            die("<div class='alert alert-danger'>Error: Admin ID tidak valid untuk log perubahan.</div>");
        }

        // Tambahkan ke log_keluhan
        $logStmt = $pdo->prepare("
            INSERT INTO log_keluhan (keluhan_id, perubahan, user_id, waktu_perubahan)
            VALUES (:keluhan_id, :perubahan, :user_id, NOW())
        ");
        $logStmt->execute([
            'keluhan_id' => $response_id,
            'perubahan' => "Status diubah menjadi '$status' dengan tanggapan: $tanggapan",
            'user_id' => $_SESSION['user_id'],
        ]);

        // Redirect untuk mencegah resubmission
        header("Location: log_keluhan.php");
        exit();
    } catch (PDOException $e) {
        die("<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>");
    }
}

// Ambil data log perubahan keluhan dari tabel log_keluhan
try {
    $stmt = $pdo->prepare("
        SELECT lk.keluhan_id, lk.perubahan, lk.waktu_perubahan, u.nama AS admin
        FROM log_keluhan lk
        JOIN users u ON lk.user_id = u.id
        ORDER BY lk.waktu_perubahan DESC
    ");
    $stmt->execute();
    $log_keluhan = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<div class='alert alert-danger'>Error: Gagal mengambil data log perubahan. " . $e->getMessage() . "</div>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Perubahan Keluhan</title>
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
                <li class="nav-item"><a href="dashboardAdm.php" class="nav-link text-white">Dashboard</a></li>
                <li class="nav-item"><a href="kelola_keluhan.php" class="nav-link text-white">Kelola Keluhan</a></li>
                <li class="nav-item"><a href="log_keluhan.php" class="nav-link text-white">Log Keluhan</a></li>
                <li class="nav-item"><a href="profile.php" class="nav-link text-white">Profile</a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link text-white">Logout</a></li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="container mt-5">
            <h1>Log Perubahan Keluhan</h1>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID Keluhan</th>
                        <th>Perubahan</th>
                        <th>Admin</th>
                        <th>Waktu Perubahan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($log_keluhan)): ?>
                        <tr>
                            <td colspan="4" class="text-center">Tidak ada log perubahan keluhan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($log_keluhan as $log): ?>
                        <tr>
                            <td><?= htmlspecialchars($log['keluhan_id']) ?></td>
                            <td><?= nl2br(htmlspecialchars($log['perubahan'])) ?></td>
                            <td><?= htmlspecialchars($log['admin']) ?></td>
                            <td><?= htmlspecialchars($log['waktu_perubahan']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
