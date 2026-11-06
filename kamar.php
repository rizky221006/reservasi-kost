<?php require 'config.php';
$title = 'Daftar Kamar';
$w = ['1=1'];
$p = [];
if (!empty($_GET['q'])) {
    $w[] = 'nama LIKE ?';
    $p[] = '%' . $_GET['q'] . '%';
}
if (!empty($_GET['tipe'])) {
    $w[] = 'tipe=?';
    $p[] = $_GET['tipe'];
}
if (!empty($_GET['maks'])) {
    $w[] = 'harga<=?';
    $p[] = (int) $_GET['maks'];
}

$s = $pdo->prepare('SELECT * FROM kamar WHERE ' . implode(' AND ', $w) . ' ORDER BY status,harga');
$s->execute($p);
$rows = $s->fetchAll();
include 'inc/header.php'; ?>
<div class="wrap ph">
    <h1>Daftar Kamar</h1>
    <p class="muted"><?= count($rows) ?> kamar ditemukan</p>
    <form style="display:flex;gap:10px;margin:20px 0;flex-wrap:wrap"><input name="q" style="flex:2"
            placeholder="Cari kamar" value="<?= e($_GET['q'] ?? '') ?>"><select name="tipe" style="flex:1">
            <option value="">Semua tipe</option>
            <?php foreach (['Putra', 'Putri', 'Campur'] as $t)
                echo '<option ' . (($_GET['tipe'] ?? '') == $t ? 'selected' : '') . '>' . $t . '</option>'; ?>
        </select><input name="maks" style="flex:1" type="number" placeholder="Harga maks"
            value="<?= e($_GET['maks'] ?? '') ?>"><button class="btn">Filter</button></form>
    <div class="grid"><?php foreach ($rows as $k)
        echo card($k);
    if (!$rows)
        echo '<p>Tidak ada kamar yang cocok.</p>'; ?>
    </div>
</div>
<?php include 'inc/footer.php'; ?>