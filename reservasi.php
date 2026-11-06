<?php
require 'config.php';

need();

$title = 'Form Reservasi';

// Mengambil data kamar berdasarkan ID
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$s = $pdo->prepare(
    "SELECT * FROM kamar WHERE id = ? AND status = 'tersedia'"
);
$s->execute([$id]);

$k = $s->fetch();

if (!$k) {
    flash('Kamar tidak tersedia.');
    header('Location: kamar.php');
    exit;
}

$err = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $d = (int) ($_POST['durasi'] ?? 0);
    $t = trim($_POST['tgl'] ?? '');
    $catatan = trim($_POST['catatan'] ?? '');

    // Validasi tanggal
    $tanggal = DateTime::createFromFormat('!Y-m-d', $t);
    $tanggalValid = $tanggal &&
        $tanggal->format('Y-m-d') === $t;

    // Validasi tanggal dan durasi
    if (
        $d < 1 ||
        $d > 24 ||
        !$tanggalValid ||
        $t < date('Y-m-d')
    ) {

        $err = 'Tanggal masuk atau durasi tidak valid.';

    } else {

        try {

            $total = $d * (int) $k['harga'];

            $pdo->beginTransaction();

            $sql = $pdo->prepare(
                'INSERT INTO reservasi
                (user_id, kamar_id, tgl_masuk, durasi, total, catatan)
                VALUES (?, ?, ?, ?, ?, ?)'
            );

            $sql->execute([
                user()['id'],
                $k['id'],
                $t,
                $d,
                $total,
                $catatan
            ]);

            $pdo->commit();

            flash('Reservasi berhasil dikirim! Menunggu konfirmasi admin.');

            header('Location: riwayat.php');
            exit;

        } catch (PDOException $e) {

            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $err = 'Reservasi gagal disimpan. Silakan coba kembali.';
        }
    }
}

include 'inc/header.php';
?>

<div class="box">

    <h2 style="text-align:left">
        Reservasi <?= e($k['nama']) ?>
    </h2>

    <p class="muted">
        <?= rp($k['harga']) ?> / bulan
    </p>

    <?php if ($err): ?>
        <div class="alert err">
            <?= e($err) ?>
        </div>
    <?php endif; ?>

    <form method="post">

        <input type="hidden" name="id" value="<?= $k['id'] ?>">

        <label>Tanggal Masuk</label>
        <input
            type="date"
            name="tgl"
            min="<?= date('Y-m-d') ?>"
            value="<?= e($_POST['tgl'] ?? '') ?>"
            required
        >

        <label>Durasi (bulan)</label>
        <input
            type="number"
            name="durasi"
            id="d"
            value="<?= e($_POST['durasi'] ?? '1') ?>"
            min="1"
            max="24"
            required
        >

        <label>Catatan</label>
        <textarea
            name="catatan"
            rows="3"
            placeholder="Permintaan khusus (opsional)"
        ><?= e($_POST['catatan'] ?? '') ?></textarea>

        <p style="margin:16px 0">
            <b>
                Estimasi total:
                <span id="tot" style="color:var(--p)"></span>
            </b>
        </p>

        <button class="btn" style="width:100%">
            Kirim Reservasi
        </button>

    </form>

</div>

<script>
const harga = <?= (int) $k['harga'] ?>;
const durasi = document.getElementById('d');
const total = document.getElementById('tot');

function hitungTotal() {
    const jumlah = parseInt(durasi.value) || 0;

    total.textContent = 'Rp ' +
        (harga * jumlah).toLocaleString('id-ID');
}

durasi.addEventListener('input', hitungTotal);

hitungTotal();
</script>

<?php include 'inc/footer.php'; ?>
```