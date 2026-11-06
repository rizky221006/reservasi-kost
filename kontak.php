<?php require 'config.php';
$title = 'Kontak';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pdo->prepare('INSERT INTO pesan(nama,email,isi) VALUES(?,?,?)')->execute([$_POST['nama'], $_POST['email'], $_POST['isi']]);
    flash('Pesan terkirim, kami akan segera membalas.');
    header('Location: kontak.php');
    exit;
}
include 'inc/header.php'; ?>
<div class="wrap ph">
    <h1>Hubungi Kami 📬</h1>
    <div class="detail" style="margin-top:20px">
        <div>
            <p class="muted">Ada pertanyaan seputar kamar atau reservasi? Kirim pesan atau kunjungi kami.</p><br>
            <p>📍 Jl. Contoh No. 10, Medan</p>
            <p>📞 0812-3456-7890</p>
            <p>✉️ halo@reservasikost.test</p>
            <p>🕘 Setiap hari 08.00 – 20.00</p>
        </div>
        <form method="post" class="box" style="margin:0"><label style="margin-top:0">Nama</label><input name="nama"
                required><label>Email</label><input type="email" name="email" required><label>Pesan</label><textarea
                name="isi" rows="4" required></textarea><br><br><button class="btn">Kirim Pesan</button></form>
    </div>
</div>
<?php include 'inc/footer.php'; ?>