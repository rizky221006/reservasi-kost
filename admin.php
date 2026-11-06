<?php
require 'config.php';

need(true);

$title = 'Dashboard Admin';

// Menyimpan foto kamar yang diunggah. Mengembalikan path relatif,
// atau null jika tidak ada file yang dipilih.
function simpanFoto(array $f): ?string
{
    $err = $f['error'] ?? UPLOAD_ERR_NO_FILE;

    if ($err === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($err !== UPLOAD_ERR_OK) {
        throw new Exception('Upload foto gagal. Coba file lain.');
    }

    if ($f['size'] > 3 * 1024 * 1024) {
        throw new Exception('Ukuran foto maksimal 3 MB.');
    }

    $info = @getimagesize($f['tmp_name']);
    $tipeGambar = $info ? $info[2] : 0;

    $ekstensi = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_WEBP => 'webp',
    ][$tipeGambar] ?? null;

    if (!$ekstensi) {
        throw new Exception('Format foto harus JPG, PNG, atau WEBP.');
    }

    $folder = __DIR__ . '/uploads/kamar';

    if (!is_dir($folder) && !mkdir($folder, 0755, true)) {
        throw new Exception('Folder uploads/kamar tidak bisa dibuat.');
    }

    $namaFile = bin2hex(random_bytes(8)) . '.' . $ekstensi;

    if (!move_uploaded_file($f['tmp_name'], $folder . '/' . $namaFile)) {
        throw new Exception('Foto gagal disimpan di server.');
    }

    return 'uploads/kamar/' . $namaFile;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    try {

        // Menerima atau menolak reservasi
        if (isset($_POST['st'])) {

            $idReservasi = (int) ($_POST['id'] ?? 0);
            $statusBaru = $_POST['st'];

            if (!in_array($statusBaru, ['dikonfirmasi', 'ditolak'], true)) {
                throw new Exception('Status reservasi tidak valid.');
            }

            $pdo->beginTransaction();

            // Ambil data reservasi
            $cek = $pdo->prepare(
                "SELECT kamar_id, status
                 FROM reservasi
                 WHERE id = ?"
            );
            $cek->execute([$idReservasi]);
            $reservasi = $cek->fetch();

            if (!$reservasi || $reservasi['status'] != 'pending') {
                throw new Exception('Reservasi tidak ditemukan atau sudah diproses.');
            }

            // Perbarui status reservasi
            $update = $pdo->prepare(
                'UPDATE reservasi SET status = ? WHERE id = ?'
            );
            $update->execute([$statusBaru, $idReservasi]);

            // Jika diterima, ubah kamar menjadi terisi
            if ($statusBaru == 'dikonfirmasi') {

                $updateKamar = $pdo->prepare(
                    "UPDATE kamar SET status = 'terisi' WHERE id = ?"
                );

                $updateKamar->execute([$reservasi['kamar_id']]);
            }

            $pdo->commit();

            flash('Status reservasi berhasil diperbarui.');
        }

        // Menghapus kamar
        elseif (isset($_POST['hapus'])) {

            $idKamar = (int) $_POST['hapus'];

            $hapus = $pdo->prepare(
                'DELETE FROM kamar WHERE id = ?'
            );

            $hapus->execute([$idKamar]);

            flash('Kamar berhasil dihapus.');
        }

        // Mengubah status kamar
        elseif (isset($_POST['toggle'])) {

            $idKamar = (int) $_POST['toggle'];

            $update = $pdo->prepare(
                "UPDATE kamar
                 SET status = CASE
                     WHEN status = 'tersedia' THEN 'terisi'
                     ELSE 'tersedia'
                 END
                 WHERE id = ?"
            );

            $update->execute([$idKamar]);

            flash('Status kamar berhasil diubah.');
        }

        // Mengganti foto kamar
        elseif (isset($_POST['foto'])) {

            $idKamar = (int) $_POST['foto'];
            $pathFoto = simpanFoto($_FILES['gambar'] ?? []);

            if ($pathFoto === null) {
                throw new Exception('Pilih foto terlebih dahulu.');
            }

            $ganti = $pdo->prepare(
                'UPDATE kamar SET gambar = ? WHERE id = ?'
            );

            $ganti->execute([$pathFoto, $idKamar]);

            flash('Foto kamar berhasil diganti.');
        }

        // Menambahkan kamar baru
        elseif (isset($_POST['tambah'])) {

            $nama = trim($_POST['nama'] ?? '');
            $tipe = $_POST['tipe'] ?? 'Putra';
            $harga = (int) ($_POST['harga'] ?? 0);
            $luas = trim($_POST['luas'] ?? '');
            $fasilitas = trim($_POST['fasilitas'] ?? '');
            $deskripsi = trim($_POST['deskripsi'] ?? '');

            if (
                $nama == '' ||
                $harga <= 0 ||
                !in_array($tipe, ['Putra', 'Putri', 'Campur'], true)
            ) {
                throw new Exception('Data kamar tidak valid.');
            }

            $pathFoto = simpanFoto($_FILES['gambar'] ?? []);

            $tambah = $pdo->prepare(
                'INSERT INTO kamar
                (nama, tipe, harga, luas, fasilitas, deskripsi, emoji, gambar)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $tambah->execute([
                $nama,
                $tipe,
                $harga,
                $luas,
                $fasilitas,
                $deskripsi,
                '',
                $pathFoto
            ]);

            flash('Kamar baru berhasil ditambahkan.');
        }

        header('Location: admin.php');
        exit;

    } catch (PDOException $e) {
        // PDOException harus di atas Exception, karena PDOException
        // adalah turunan Exception (kalau terbalik, blok ini tidak pernah jalan)

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        flash('Operasi gagal. Periksa relasi data atau koneksi database.');

        header('Location: admin.php');
        exit;

    } catch (Exception $e) {

        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        flash('Gagal: ' . $e->getMessage());

        header('Location: admin.php');
        exit;
    }
}

// Mengambil statistik dashboard
$st = $pdo->query(
    "SELECT
        (SELECT COUNT(*) FROM kamar) AS k,
        (SELECT COUNT(*) FROM reservasi WHERE status = 'pending') AS p,
        (SELECT COUNT(*) FROM users WHERE role = 'user') AS u,
        (SELECT COALESCE(SUM(total), 0)
         FROM reservasi
         WHERE status = 'dikonfirmasi') AS t"
)->fetch();

// Mengambil seluruh data reservasi
$res = $pdo->query(
    'SELECT
        r.*,
        k.nama AS kn,
        u.nama AS un
     FROM reservasi r
     JOIN kamar k ON k.id = r.kamar_id
     JOIN users u ON u.id = r.user_id
     ORDER BY r.id DESC'
)->fetchAll();

// Mengambil data kamar
$km = $pdo->query(
    'SELECT * FROM kamar ORDER BY id'
)->fetchAll();

// Mengambil lima pesan terbaru
$ps = $pdo->query(
    'SELECT * FROM pesan ORDER BY id DESC LIMIT 5'
)->fetchAll();

include 'inc/header.php';
?>

<div class="wrap ph">

    <h1>Dashboard Admin 🛠️</h1>
    <br>

    <?php if ($pesan = flash()): ?>
        <div class="alert">
            <?= e($pesan) ?>
        </div>
    <?php endif; ?>

    <!-- Statistik Dashboard -->
    <div class="grid4">

        <div class="feat">
            <b><?= $st['k'] ?></b><br>
            Kamar
        </div>

        <div class="feat">
            <b><?= $st['p'] ?></b><br>
            Menunggu Konfirmasi
        </div>

        <div class="feat">
            <b><?= $st['u'] ?></b><br>
            Penghuni Terdaftar
        </div>

        <div class="feat">
            <b><?= rp($st['t']) ?></b><br>
            Pendapatan Terkonfirmasi
        </div>

    </div>

    <!-- Tabel Reservasi -->
    <h2 style="text-align:left;margin:34px 0 14px">
        Reservasi
    </h2>

    <div class="tbl">
        <table>

            <tr>
                <th>#</th>
                <th>Penyewa</th>
                <th>Kamar</th>
                <th>Masuk</th>
                <th>Total</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>

            <?php foreach ($res as $r): ?>

                <tr>

                    <td><?= $r['id'] ?></td>

                    <td><?= e($r['un']) ?></td>

                    <td><?= e($r['kn']) ?></td>

                    <td><?= e($r['tgl_masuk']) ?></td>

                    <td><?= rp($r['total']) ?></td>

                    <td>
                        <span class="badge <?= e($r['status']) ?>">
                            <?= e($r['status']) ?>
                        </span>
                    </td>

                    <td>

                        <?php if ($r['status'] == 'pending'): ?>

                            <form class="inline" method="post">

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= $r['id'] ?>"
                                >

                                <button
                                    class="btn sm"
                                    name="st"
                                    value="dikonfirmasi"
                                >
                                    Terima
                                </button>

                                <button
                                    class="btn sm red"
                                    name="st"
                                    value="ditolak"
                                >
                                    Tolak
                                </button>

                            </form>

                        <?php endif; ?>

                    </td>

                </tr>

            <?php endforeach; ?>

        </table>
    </div>

    <!-- Kelola Kamar -->
    <h2 style="text-align:left;margin:34px 0 14px">
        Kelola Kamar
    </h2>

    <div class="tbl">
        <table>

            <tr>
                <th>Kamar</th>
                <th>Tipe</th>
                <th>Harga</th>
                <th>Foto</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>

            <?php foreach ($km as $k): ?>

                <tr>

                    <td>
                        <?= e($k['nama']) ?>
                    </td>

                    <td><?= e($k['tipe']) ?></td>

                    <td><?= rp($k['harga']) ?></td>

                    <td>
                        <?php if (!empty($k['gambar'])): ?>
                            <img
                                src="<?= e($k['gambar']) ?>"
                                alt="<?= e($k['nama']) ?>"
                                style="width:70px;height:50px;object-fit:cover;border-radius:6px;display:block;margin-bottom:6px"
                            >
                        <?php endif; ?>

                        <form
                            class="inline"
                            method="post"
                            enctype="multipart/form-data"
                        >
                            <input
                                type="file"
                                name="gambar"
                                accept="image/jpeg,image/png,image/webp"
                                required
                                style="max-width:170px"
                            >

                            <button
                                class="btn sm"
                                name="foto"
                                value="<?= $k['id'] ?>"
                            >
                                Ganti Foto
                            </button>
                        </form>
                    </td>

                    <td>
                        <span class="badge <?= e($k['status']) ?>">
                            <?= e($k['status']) ?>
                        </span>
                    </td>

                    <td>

                        <form class="inline" method="post">

                            <button
                                class="btn sm alt"
                                name="toggle"
                                value="<?= $k['id'] ?>"
                            >
                                Ubah Status
                            </button>

                            <button
                                class="btn sm red"
                                name="hapus"
                                value="<?= $k['id'] ?>"
                                onclick="return confirm('Hapus kamar ini?')"
                            >
                                Hapus
                            </button>

                        </form>

                    </td>

                </tr>

            <?php endforeach; ?>

        </table>
    </div>

    <!-- Form Tambah Kamar -->
    <form
        method="post"
        enctype="multipart/form-data"
        class="box wide"
        style="margin:0 0 30px"
    >

        <h3>➕ Tambah Kamar</h3>

        <div class="grid">

            <div>
                <label>Nama</label>
                <input name="nama" required>
            </div>

            <div>
                <label>Tipe</label>

                <select name="tipe">
                    <option>Putra</option>
                    <option>Putri</option>
                    <option>Campur</option>
                </select>
            </div>

            <div>
                <label>Harga/bulan</label>
                <input type="number" name="harga" min="1" required>
            </div>

            <div>
                <label>Luas</label>
                <input name="luas" placeholder="3x4 m">
            </div>

            <div>
                <label>Fasilitas (pisah koma)</label>
                <input name="fasilitas">
            </div>

            <div>
                <label>Foto Kamar (JPG/PNG/WEBP, maks 3 MB)</label>
                <input
                    type="file"
                    name="gambar"
                    accept="image/jpeg,image/png,image/webp"
                >
            </div>

        </div>

        <label>Deskripsi</label>

        <textarea name="deskripsi" rows="2"></textarea>

        <br><br>

        <button class="btn" name="tambah" value="1">
            Simpan Kamar
        </button>

    </form>

    <!-- Pesan Masuk -->
    <h2 style="text-align:left;margin-bottom:14px">
        Pesan Masuk
    </h2>

    <?php foreach ($ps as $p): ?>

        <div class="testi" style="margin-bottom:10px">

            <b><?= e($p['nama']) ?></b>

            <span class="muted">
                (<?= e($p['email']) ?>)
            </span>

            <br>

            <?= e($p['isi']) ?>

        </div>

    <?php endforeach; ?>

    <?php if (!$ps): ?>

        <p class="muted">Belum ada pesan.</p>

    <?php endif; ?>

</div>

<?php include 'inc/footer.php'; ?>