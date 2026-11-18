import "./bootstrap";
import "@hotwired/turbo";

import Alpine from "alpinejs";
import Swal from "sweetalert2";

window.Alpine = Alpine;
window.Swal = Swal;

// Integrasi Alpine.js dengan Turbo Drive:
// Turbo mengganti <body> tanpa full reload, sehingga Alpine perlu
// di-reinitialize setiap kali Turbo selesai merender halaman baru.
document.addEventListener("turbo:before-render", (event) => {
    if (window.Alpine) {
        // Hentikan Alpine di halaman lama sebelum Turbo mengganti body
        Alpine.destroyTree(document.body);
    }
});

document.addEventListener("turbo:render", () => {
    if (window.Alpine) {
        Alpine.initTree(document.body);
    }
});

Alpine.start();

document.addEventListener("submit", function (event) {
    const form = event.target;

    if (!form.matches("[data-confirm-delete]")) {
        return;
    }

    event.preventDefault();

    Swal.fire({
        title: "Hapus data?",
        text: "Data yang sudah dihapus tidak dapat dikembalikan.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, hapus",
        cancelButtonText: "Batal",
        confirmButtonColor: "#dc2626",
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});
