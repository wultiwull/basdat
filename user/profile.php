<?php
include "../includes/auth.php";
requireLogin();
include "../includes/config.php";

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Update profile
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = htmlspecialchars($_POST['nama']);
    $email = htmlspecialchars($_POST['email']);
    $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $user['password'];

    $stmt = $pdo->prepare("UPDATE users SET nama = :nama, email = :email, password = :password WHERE id = :id");
    $stmt->execute([
        'nama' => $nama,
        'email' => $email,
        'password' => $password,
        'id' => $_SESSION['user_id']
    ]);

    $success = "Profile berhasil diperbarui!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Profile User</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Profile</h2>
        <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
        <form method="POST">
            <div class="form-group">
                <label>Nama:</label>
                <input type="text" name="nama" class="form-control" value="<?= $user['nama'] ?>" required>
            </div>
            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" class="form-control" value="<?= $user['email'] ?>" required>
            </div>
            <div class="form-group">
                <label>Password (kosongkan jika tidak ingin mengubah):</label>
                <input type="password" name="password" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
        <h3 class="mt-5">Riwayat Keluhan</h3>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Judul</th>
                    <th>Deskripsi</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $complaints = $pdo->prepare("SELECT * FROM keluhan WHERE user_id = :id");
                $complaints->execute(['id' => $_SESSION['user_id']]);
                foreach ($complaints->fetchAll(PDO::FETCH_ASSOC) as $complaint):
                ?>
                <tr>
                    <td><?= $complaint['id'] ?></td>
                    <td><?= $complaint['judul'] ?></td>
                    <td><?= $complaint['deskripsi'] ?></td>
                    <td><?= ucfirst($complaint['status']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
