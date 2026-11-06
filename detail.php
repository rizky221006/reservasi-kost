<?php require 'config.php';
$s = $pdo->prepare('SELECT * FROM kamar WHERE id=?');
$s->execute([(int) ($_GET['id'] ?? 0)]);
$k = $s->fetch();
if (!$k) {
    header('Location: kamar.php');
    exit;
}
$title = $k['nama'];
include 'inc/header.php'; ?>
<div class="wrap detail">
      <div class="thumb">
        <?php if (!empty($k['gambar'])): ?>
            <img src="<?= e($k['gambar']) ?>" alt="<?= e($k['nama']) ?>">
        <?php else: ?>
            <?= e($k['emoji']) ?>
        <?php endif; ?>
    </div>

    <div>
        <span class="badge <?= $k['status'] ?>"><?= ucfirst($k['status']) ?></span>
        <span class="tag">Kost <?= e($k['tipe']) ?></span>

        <h1 style="margin:10px 0">Kamar <?= e($k['nama']) ?></h1>

        <h2 style="text-align:left;color:var(--p)">
            <?= rp($k['harga']) ?>
            <small class="muted" style="font-size:1rem">/bulan</small>
        </h2>

        <p style="margin:14px 0"><?= e($k['deskripsi']) ?></p>

        <p><b>Ukuran:</b> <?= e($k['luas']) ?></p>

        <h4 style="margin-top:14px">Fasilitas</h4>

        <div class="chips">
            <?php foreach (explode(',', $k['fasilitas']) as $f)
                echo '<span>✔ ' . e(trim($f)) . '</span>'; ?>
        </div>

        <br>

        <?php if ($k['status'] == 'tersedia'): ?>
            <a class="btn" href="reservasi.php?id=<?= $k['id'] ?>">
                Reservasi Sekarang
            </a>
        <?php else: ?>
            <button class="btn alt" disabled>Sudah Terisi</button>
        <?php endif; ?>

        <a href="kamar.php" class="muted">← Kembali</a>
    </div>
</div>

<?php include 'inc/footer.php'; ?>