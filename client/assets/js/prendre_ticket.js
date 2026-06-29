document.addEventListener('DOMContentLoaded', function () {

    var payBtn = document.getElementById('payBtn');
    var generateBtn = document.getElementById('generateBtn');
    var paymentSection = document.getElementById('paymentSection');
    var paymentStatus = document.getElementById('paymentStatus');

    var isPaid = false;

    /* ---------- PAIEMENT ---------- */
    payBtn.addEventListener('click', function () {

        isPaid = true;

        payBtn.disabled = true;
        payBtn.innerHTML = '<i class="fas fa-check"></i> Payé';

        paymentSection.classList.add('paid');

        paymentStatus.innerHTML = '<span class="badge success">Payé</span>';

        generateBtn.disabled = false;

    });

});