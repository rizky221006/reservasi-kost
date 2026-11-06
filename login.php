
<?php
require 'config.php';

$title = 'Masuk';
$err = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $s = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $s->execute([$email]);

    $u = $s->fetch();

    $ok = false;

    if ($u) {
        $storedPassword = $u['password'];

        // Memeriksa apakah password sudah berbentuk hash
        $info = password_get_info($storedPassword);

        if (($info['algoName'] ?? 'unknown') !== 'unknown') {

            $ok = password_verify($password, $storedPassword);

        } else {

            // Mendukung password lama yang belum di-hash
            if ($storedPassword === $password) {

                $ok = true;

                // Mengubah password lama menjadi hash
                $hashedPassword = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $update = $pdo->prepare(
                    'UPDATE users SET password = ? WHERE id = ?'
                );

                $update->execute([$hashedPassword, $u['id']]);

                $u['password'] = $hashedPassword;
            }
        }
    }

    if ($ok) {

        // Jangan menyimpan password ke session
        unset($u['password']);

        $_SESSION['u'] = $u;

        flash('Selamat datang, ' . $u['nama'] . '!');

        header(
            'Location: ' .
            ($u['role'] == 'admin' ? 'admin.php' : 'index.php')
        );

        exit;

    } else {

        $err = 'Email atau password salah.';
    }
}

include 'inc/header.php';
?>

<main class="login-page">

    <div class="box login-box">

        <h2>Masuk</h2>

        <p class="sub">Akses reservasi kostmu</p>

        <?php if ($err): ?>
            <div class="alert err">
                <?= e($err) ?>
            </div>
        <?php endif; ?>

        <form method="post">

            <label>Email</label>
            <input
                type="email"
                name="email"
                required
            >

            <label>Password</label>
            <input
                type="password"
                name="password"
                required
            >

            <br><br>

            <button class="btn" style="width:100%">
                Masuk
            </button>

        </form>

        <p class="muted" style="text-align:center;margin-top:14px">

            Belum punya akun?
            <a href="register.php"><b>Daftar</b></a>

        </p>

    </div>

</main>

<?php include 'inc/footer.php'; ?>
