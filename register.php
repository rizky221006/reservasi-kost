
<?php
require 'config.php';

$title = 'Daftar';
$err = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $hp = trim($_POST['hp'] ?? '');
    $password = $_POST['password'] ?? '';

    if (strlen($password) < 6) {

        $err = 'Password minimal 6 karakter.';

    } else {

        try {
            // Memeriksa email yang sudah terdaftar
            $c = $pdo->prepare(
                'SELECT id FROM users WHERE email = ?'
            );

            $c->execute([$email]);

            if ($c->fetch()) {

                $err = 'Email sudah terdaftar.';

            } else {

                // Menyimpan akun baru ke Supabase
                $hashedPassword = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $insert = $pdo->prepare(
                    'INSERT INTO users (nama, email, hp, password, role)
                     VALUES (?, ?, ?, ?, ?)'
                );

                $insert->execute([
                    $nama,
                    $email,
                    $hp,
                    $hashedPassword,
                    'user'
                ]);

                flash('Akun berhasil dibuat, silakan masuk.');

                header('Location: login.php');
                exit;
            }

        } catch (PDOException $e) {

            $err = 'Pendaftaran gagal. Silakan coba kembali.';
        }
    }
}

include 'inc/header.php';
?>

<main class="register-page">

    <div class="box register-box">

        <h2>Buat Akun</h2>

        <p class="sub">Gratis dan cepat</p>

        <?php if ($err): ?>
            <div class="alert err">
                <?= e($err) ?>
            </div>
        <?php endif; ?>

        <form method="post">

            <label>Nama Lengkap</label>
            <input
                type="text"
                name="nama"
                required
            >

            <label>Email</label>
            <input
                type="email"
                name="email"
                required
            >

            <label>No. HP</label>
            <input
                type="text"
                name="hp"
                required
            >

            <label>Password</label>
            <input
                type="password"
                name="password"
                minlength="6"
                required
            >

            <br><br>

            <button class="btn" style="width:100%">
                Daftar
            </button>

        </form>

        <p class="muted" style="text-align:center;margin-top:14px">
            Sudah punya akun?
            <a href="login.php"><b>Masuk</b></a>
        </p>

    </div>

</main>

<?php include 'inc/footer.php'; ?>
