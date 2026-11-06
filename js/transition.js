
document.addEventListener("DOMContentLoaded", function () {

    const navigation = document.querySelector("#liquidNavigation");

    const indicator = navigation
        ? navigation.querySelector(".indicator")
        : null;

    function moveIndicator(item) {
        if (!navigation || !indicator || !item) return;

        const navRect = navigation.getBoundingClientRect();
        const itemRect = item.getBoundingClientRect();

        const position =
            itemRect.left - navRect.left +
            (itemRect.width - indicator.offsetWidth) / 2;

        indicator.style.transform = `translateX(${position}px)`;
    }

    // Posisi awal lingkaran
    if (navigation) {
    const activeItem = navigation.querySelector(".list.active");

    if (activeItem) {
        requestAnimationFrame(function () {
            moveIndicator(activeItem);
        });
    }

    // Lingkaran bergerak saat kursor diarahkan ke menu
    navigation.querySelectorAll(".list").forEach(function (item) {
        item.addEventListener("mouseenter", function () {
            moveIndicator(this);
        });
    });

    // Lingkaran kembali ke menu aktif saat kursor keluar
    navigation.addEventListener("mouseleave", function () {
        const active = navigation.querySelector(".list.active");

        if (active) {
            moveIndicator(active);
        }
    });
}

    // Klik semua tautan
    document.querySelectorAll("a[href]").forEach(function (link) {

        link.addEventListener("click", function (e) {

            const tujuan = this.getAttribute("href");

            if (
                !tujuan ||
                tujuan.startsWith("#") ||
                tujuan.startsWith("javascript:") ||
                this.target === "_blank" ||
                e.ctrlKey ||
                e.metaKey ||
                e.shiftKey ||
                e.altKey
            ) {
                return;
            }

            const url = new URL(this.href, window.location.href);

            if (url.origin !== window.location.origin) {
                return;
            }

            e.preventDefault();

            // Animasi lingkaran Liquid Navigation
            if (
                navigation &&
                this.classList.contains("list")
            ) {
                navigation.querySelectorAll(".list").forEach(function (item) {
                    item.classList.remove("active");
                });

                this.classList.add("active");

                moveIndicator(this);
            }

            // Transisi perpindahan halaman
            document.body.classList.add("page-exit");

            setTimeout(function () {
                window.location.href = url.href;
            }, 400);

        });

    });

    // Menyesuaikan posisi lingkaran ketika layar berubah
    window.addEventListener("resize", function () {

        if (!navigation) return;

        const activeItem = navigation.querySelector(".list.active");

        if (activeItem) {
            moveIndicator(activeItem);
        }

    });

});
