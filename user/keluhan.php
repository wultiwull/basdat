<?php
include "../includes/auth.php";
requireLogin();
include "../includes/config.php";

// Ambil kategori keluhan
$stmt = $pdo->query("SELECT id, nama_kategori FROM kategori_keluhan WHERE status = 'aktif'");
$kategori = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Proses form jika ada kiriman POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = htmlspecialchars($_POST['judul']);
    $deskripsi = htmlspecialchars($_POST['deskripsi']);
    $kategori_id = htmlspecialchars($_POST['kategori_id']);
    $user_id = $_SESSION['user_id'];

    // Simpan keluhan ke database
    $stmt = $pdo->prepare("INSERT INTO keluhan (user_id, kategori_id, judul, deskripsi, status, created_at, updated_at) 
                           VALUES (:user_id, :kategori_id, :judul, :deskripsi, 'pending', NOW(), NOW())");
    $stmt->execute([
        'user_id' => $user_id,
        'kategori_id' => $kategori_id,
        'judul' => $judul,
        'deskripsi' => $deskripsi
    ]);

    // Redirect ke dashboard setelah laporan berhasil
    header("Location: dashboard.php?success=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Laporkan Keluhan</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="card mx-auto" style="max-width: 600px;">
            <div class="card-body">
                <h2 class="card-title text-center">Laporkan Keluhan</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="kategori">Kategori Keluhan:</label>
                        <select name="kategori_id" id="kategori" class="form-control" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php foreach ($kategori as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= $k['nama_kategori'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="judul">Judul:</label>
                        <input type="text" name="judul" id="judul" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="deskripsi">Deskripsi:</label>
                        <textarea name="deskripsi" id="deskripsi" class="form-control" rows="4" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Laporkan</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
