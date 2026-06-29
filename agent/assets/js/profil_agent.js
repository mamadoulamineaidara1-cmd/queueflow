document.addEventListener('DOMContentLoaded', function () {

    const editModal = document.getElementById("editProfileModal");
    const passwordModal = document.getElementById("passwordModal");

    // Ouvrir modal modifier profil
    document.getElementById("openEditProfile").onclick = () => {
        editModal.classList.add("show");
    };

    // Ouvrir modal mot de passe
    document.getElementById("openPasswordModal").onclick = () => {
        passwordModal.classList.add("show");
    };

    // Fermer modals en cliquant sur la croix
    document.querySelectorAll(".close-modal").forEach(btn => {
        btn.addEventListener("click", function () {
            this.closest(".modal-overlay").classList.remove("show");
        });
    });

    // Fermer modal en cliquant sur le bouton Annuler
    document.querySelectorAll(".btn-cancel").forEach(btn => {
        btn.addEventListener("click", function () {
            this.closest(".modal-overlay").classList.remove("show");
        });
    });

    // Fermer modal en cliquant à l'extérieur
    window.addEventListener("click", function (e) {
        if (e.target.classList.contains("modal-overlay")) {
            e.target.classList.remove("show");
        }
    });

});