<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?= e($title ?? 'Pondok Qonitaat') ?> – Pondok Qonitaat</title>
    <link rel="stylesheet" href="assets/style.css">
</head>

<body>

<?php
$u = user();
$namaUser = $u['nama'] ?? $u['name'] ?? $u['email'] ?? 'Pengguna';
$hal = basename($_SERVER['PHP_SELF']);
?>

<!-- 1. HEADER -->
<header class="nav">
    <div class="wrap bar">
        <a class="logo" href="index.php">🏡 Pondok<b>Qonitaat</b></a>
        <p class="tagline">Hunian kos nyaman, reservasi mudah</p>
    </div>

    <!-- 2. NAVIGASI -->
    <div class="navband">
        <nav class="navigation" id="liquidNavigation">

            <span class="indicator"></span>

            <a href="index.php" class="list <?= $hal == 'index.php' ? 'active' : '' ?>">
                <span class="icon">⌂</span>
                <span class="text">Beranda</span>
            </a>

            <a href="kamar.php" class="list <?= $hal == 'kamar.php' ? 'active' : '' ?>">
                <span class="icon">▣</span>
                <span class="text">Kamar</span>
            </a>

            <a href="tentang.php" class="list <?= $hal == 'tentang.php' ? 'active' : '' ?>">
                <span class="icon">ⓘ</span>
                <span class="text">Tentang</span>
            </a>

            <a href="kontak.php" class="list <?= $hal == 'kontak.php' ? 'active' : '' ?>">
                <span class="icon">✉</span>
                <span class="text">Kontak</span>
            </a>

            <?php if ($u): ?>

                <a href="riwayat.php" class="list <?= $hal == 'riwayat.php' ? 'active' : '' ?>">
                    <span class="icon">▤</span>
                    <span class="text">Reservasi</span>
                </a>

                <?php if ($u['role'] == 'admin'): ?>
                    <a href="admin.php" class="list <?= $hal == 'admin.php' ? 'active' : '' ?>">
                        <span class="icon">⚙</span>
                        <span class="text">Admin</span>
                    </a>
                <?php endif; ?>

                <a href="logout.php" class="list">
                    <span class="icon">⇥</span>
                    <span class="text">Keluar</span>
                </a>

            <?php endif; ?>

        </nav>
    </div>
</header>

<div class="layout">

    <!-- 3. SIDEBAR -->
    <aside class="sidebar">

        <div class="side-box">
            <h4>Akun Saya</h4>

            <?php if ($u): ?>
                <div class="side-user">
                    <span class="side-avatar"><?= e(mb_strtoupper(mb_substr($namaUser, 0, 1))) ?></span>
                    <div>
                        <b><?= e($namaUser) ?></b>
                        <small><?= e($u['role'] ?? '') ?></small>
                    </div>
                </div>
                <a class="side-link" href="riwayat.php">Riwayat Reservasi</a>
                <?php if ($u['role'] == 'admin'): ?>
                    <a class="side-link" href="admin.php">Panel Admin</a>
                <?php endif; ?>
                <a class="side-link" href="logout.php">Keluar</a>
            <?php else: ?>
                <p class="side-note">Masuk untuk memesan kamar dan melihat riwayat reservasi.</p>
                <a class="btn sm" href="login.php">Masuk</a>
                <a class="btn alt sm" href="register.php">Daftar</a>
            <?php endif; ?>
        </div>

        <div class="side-box">
            <h4>Cari Cepat</h4>
            <a class="side-link" href="kamar.php?tipe=Putra">Kamar Putra</a>
            <a class="side-link" href="kamar.php?tipe=Putri">Kamar Putri</a>
            <a class="side-link" href="kamar.php?tipe=Campur">Kamar Campur</a>
            <a class="side-link" href="kamar.php?maks=1000000">Harga ≤ Rp 1 jt</a>
        </div>

        <div class="side-box">
            <h4>Butuh Bantuan?</h4>
            <p class="side-note">Tanya ketersediaan kamar atau cara reservasi langsung ke pengelola.</p>
            <a class="btn sm" href="kontak.php">Hubungi Kami</a>
        </div>

    </aside>

    <!-- 4. KONTEN (ditutup di inc/footer.php) -->
    <div class="content">