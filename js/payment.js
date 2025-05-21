const paymentMethods = document.querySelectorAll('input[name="payment"]');
const creditCardForm = document.querySelector('.credit-card-form');
const paypalForm = document.querySelector('.paypal-form');
const bankTransferForm = document.querySelector('.bank-transfer-form');
const submitBtn = document.querySelector('.submit-btn');

paymentMethods.forEach((paymentMethod) => {
    paymentMethod.addEventListener('change', () => {
        // Hide all forms initially
        creditCardForm.style.display = 'none';
        paypalForm.style.display = 'none';
        bankTransferForm.style.display = 'none';

        // Show the selected form
        if (paymentMethod.value === 'credit-card') {
            creditCardForm.style.display = 'block';
        } else if (paymentMethod.value === 'paypal') {
            paypalForm.style.display = 'block';
        } else if (paymentMethod.value === 'bank-transfer') {
            bankTransferForm.style.display = 'block';
        }
    });
});

// Function to validate expiry date format MM/YY
function isValidExpiryDate(date) {
    const regex = /^(0[1-9]|1[0-2])\/\d{2}$/;
    return regex.test(date);
}

// Form validation before submitting
submitBtn.addEventListener('click', function (e) {
    e.preventDefault(); // Prevent form submission

    const selectedPaymentMethod = document.querySelector('input[name="payment"]:checked').value;
    let isValid = true;
    let successMessage = "";

    if (selectedPaymentMethod === 'credit-card') {
        const cardNumber = document.getElementById('card-number').value;
        const cardName = document.getElementById('card-name').value;
        const expiryDate = document.getElementById('expiry-date').value;
        const cvv = document.getElementById('cvv').value;

        if (!cardNumber || !cardName || !expiryDate || !cvv) {
            isValid = false;
            alert("Please fill in all credit card details.");
        } else if (!isValidExpiryDate(expiryDate)) {
            isValid = false;
            alert("Please enter a valid expiry date in MM/YY format.");
        } else {
            successMessage = "Credit card payment successful!";
        }
    } else if (selectedPaymentMethod === 'paypal') {
        const paypalEmail = document.getElementById('paypal-email').value;
        const paypalAmount = document.getElementById('paypal-amount').value;

        if (!paypalEmail || !paypalAmount) {
            isValid = false;
            alert("Please fill in your PayPal details.");
        } else {
            successMessage = "PayPal payment successful!";
        }
    } else if (selectedPaymentMethod === 'bank-transfer') {
        const bankName = document.getElementById('bank-name').value;
        const bankAmount = document.getElementById('bank-amount').value;

        if (!bankName || !bankAmount) {
            isValid = false;
            alert("Please fill in your bank transfer details.");
        } else {
            successMessage = "Bank transfer payment successful!";
        }
    }

    if (isValid) {
        // Show success message
        alert(successMessage);

        // Redirect to another page after 2 seconds (for a better user experience)
        setTimeout(() => {
            window.location.href = "success.php"; // Change this to your target page
        }, 400); // 2000 ms delay before redirection
    }
});
