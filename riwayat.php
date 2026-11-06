<?php require 'config.php';
need();
$title = 'Reservasi Saya';
if (isset($_POST['batal'])) {
    $pdo->prepare("UPDATE reservasi SET status='dibatalkan' WHERE id=? AND user_id=? AND status='pending'")->execute([(int) $_POST['batal'], user()['id']]);
    flash('Reservasi dibatalkan.');
    header('Location: riwayat.php');
    exit;
}
$s = $pdo->prepare('SELECT r.*,k.nama,k.emoji FROM reservasi r JOIN kamar k ON k.id=r.kamar_id WHERE r.user_id=? ORDER BY r.id DESC');
$s->execute([user()['id']]);
$rows = $s->fetchAll();
include 'inc/header.php'; ?>
<div class="wrap ph">
    <h1>Reservasi Saya 📋</h1><br>
    <div class="tbl">
        <table>
            <tr>
                <th>#</th>
                <th>Kamar</th>
                <th>Masuk</th>
                <th>Durasi</th>
                <th>Total</th>
                <th>Status</th>
                <th></th>
            </tr>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= $r['id'] ?></td>
                    <td><?= e($r['emoji'] . ' ' . $r['nama']) ?></td>
                    <td><?= date('d M Y', strtotime($r['tgl_masuk'])) ?></td>
                    <td><?= $r['durasi'] ?> bln</td>
                    <td><?= rp($r['total']) ?></td>
                    <td><span class="badge <?= $r['status'] ?>"><?= $r['status'] ?></span></td>
                    <td><?php if ($r['status'] == 'pending'): ?>
                            <form class="inline" method="post" onsubmit="return confirm('Batalkan reservasi?')"><button
                                    class="btn sm red" name="batal" value="<?= $r['id'] ?>">Batalkan</button></form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach;
            if (!$rows)
                echo '<tr><td colspan="7">Belum ada reservasi. <a href="kamar.php"><b>Cari kamar</b></a></td></tr>'; ?>
        </table>
    </div>
</div>
<?php include 'inc/footer.php'; ?>