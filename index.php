<?php
require 'config.php';

$title = 'Beranda';

// Mengambil 3 kamar yang masih tersedia
$fav = $pdo->query("
    SELECT *
    FROM kamar
    WHERE status = 'tersedia'
    ORDER BY harga DESC
    LIMIT 3
")->fetchAll();

// Menghitung total kamar dan kamar tersedia
$n = $pdo->query("
    SELECT
        COUNT(*) AS c,
        COUNT(*) FILTER (WHERE status = 'tersedia') AS t
    FROM kamar
")->fetch();

include 'inc/header.php';
?>

<main class="home-page">

    <div class="hero">
        <div class="wrap">
            <h1>Temukan Kost Nyaman,<br>Reservasi dalam Hitungan Menit</h1>

            <p>Kamar bersih, harga transparan, pesan online tanpa survei berulang.</p>

            <form class="search" action="kamar.php">
                <input name="q" placeholder="🔍 Cari nama kamar...">

                <select name="tipe">
                    <option value="">Semua tipe</option>
                    <option>Putra</option>
                    <option>Putri</option>
                    <option>Campur</option>
                </select>

                <select name="maks">
                    <option value="">Semua harga</option>
                    <option value="1000000">≤ Rp 1 jt</option>
                    <option value="1250000">≤ Rp 1,25 jt</option>
                </select>

                <button class="btn">Cari</button>
            </form>
        </div>
    </div>

    <div class="wrap stats">
        <div>
            <b><?= $n['c'] ?></b>
            Total Kamar
        </div>

        <div>
            <b><?= $n['t'] ?></b>
            Kamar Tersedia
        </div>

        <div>
            <b>24 Jam</b>
            Keamanan CCTV
        </div>

        <div>
            <b>4.9★</b>
            Rating Penghuni
        </div>
    </div>

    <section>
        <div class="wrap">
            <h2>Kenapa Pilih Kami?</h2>

            <p class="sub">Fasilitas lengkap untuk hunian yang rasanya seperti rumah</p>

            <div class="grid4">

                <div class="feat">
                    <i>📶</i>
                    <h3>WiFi Kencang</h3>
                    <p class="muted">Internet cepat untuk kuliah dan kerja.</p>
                </div>

                <div class="feat">
                    <i>🔒</i>
                    <h3>Aman 24 Jam</h3>
                    <p class="muted">CCTV dan akses kunci pribadi.</p>
                </div>

                <div class="feat">
                    <i>🧺</i>
                    <h3>Bersih &amp; Rapi</h3>
                    <p class="muted">Kebersihan area umum rutin.</p>
                </div>

                <div class="feat">
                    <i>💸</i>
                    <h3>Harga Jelas</h3>
                    <p class="muted">Tanpa biaya tersembunyi.</p>
                </div>

            </div>
        </div>
    </section>

    <section style="background:#fff">
        <div class="wrap">
            <h2>Kamar Pilihan</h2>

            <p class="sub">Favorit penghuni yang masih tersedia</p>

            <div class="grid">
                <?php foreach ($fav as $k): ?>
                    <?= card($k) ?>
                <?php endforeach; ?>
            </div>

            <p style="text-align:center;margin-top:30px">
                <a class="btn" href="kamar.php">Lihat Semua Kamar →</a>
            </p>
        </div>
    </section>

    <section>
        <div class="wrap">
            <h2>Cara Reservasi</h2>

            <p class="sub">Tiga langkah mudah</p>

            <div class="steps">

                <div class="step">
                    <span>1</span>
                    <h3>Pilih Kamar</h3>
                    <p class="muted">Telusuri kamar sesuai budget.</p>
                </div>

                <div class="step">
                    <span>2</span>
                    <h3>Isi Form</h3>
                    <p class="muted">Tentukan tanggal masuk dan durasi.</p>
                </div>

                <div class="step">
                    <span>3</span>
                    <h3>Konfirmasi</h3>
                    <p class="muted">Admin mengonfirmasi pesananmu.</p>
                </div>

            </div>
        </div>
    </section>

    <section style="background:#fff">
        <div class="wrap">
            <h2>Kata Mereka 💬</h2>

            <p class="sub">&nbsp;</p>

            <div class="grid">

                <div class="testi">
                    “Prosesnya cepat banget, besoknya langsung pindah!”
                    <br>
                    <b>— Rina, Mahasiswi</b>
                </div>

                <div class="testi">
                    “Kamar sesuai foto, pemilik ramah dan responsif.”
                    <br>
                    <b>— Dimas, Karyawan</b>
                </div>

                <div class="testi">
                    “WiFi stabil, cocok buat kerja remote.”
                    <br>
                    <b>— Arif, Freelancer</b>
                </div>

            </div>
        </div>
    </section>

    <section>
        <div class="wrap">
            <div class="cta">
                <h2>Siap Pindah ke Kost Baru?</h2>

                <p style="margin-bottom:20px">
                    Daftar sekarang dan amankan kamarmu.
                </p>

                <a class="btn" href="register.php">Daftar Gratis</a>
            </div>
        </div>
    </section>

</main>

<?php include 'inc/footer.php'; ?>
