<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Method</title>
    <link rel="stylesheet" href="payment.css">
</head>
<body>

    <section class="payment-method" id="payment-method">
        <div class="container">
            <h1 class="heading">Choose Your Payment Method</h1>
            
            <div class="payment-options">
                <div class="payment-option">
                    <input type="radio" id="credit-card" name="payment" value="credit-card" checked>
                    <label for="credit-card">
                        <img src="images/visa.jpg" alt="Credit Card">
                        Credit Card
                    </label>
                </div>

                <div class="payment-option">
                    <input type="radio" id="paypal" name="payment" value="paypal">
                    <label for="paypal">
                        <img src="images/paypal.jpg" alt="PayPal">
                        PayPal
                    </label>
                </div>

                <div class="payment-option">
                    <input type="radio" id="bank-transfer" name="payment" value="bank-transfer">
                    <label for="bank-transfer">
                        <img src="images/bank_transfer.jpg" alt="Bank Transfer">
                        Bank Transfer
                    </label>
                </div>
            </div>

            <!-- Credit Card Form -->
            <div class="credit-card-form">
                <h3>Enter Credit Card Details</h3>
                <form id="payment-form">
                    <div class="input-group">
                        <label for="card-number">Card Number</label>
                        <input type="text" id="card-number" name="card-number" placeholder="1234 5678 9012 3456" required>
                    </div>
                    <div class="input-group">
                        <label for="card-name">Cardholder's Name</label>
                        <input type="text" id="card-name" name="card-name" placeholder="John Doe" required>
                    </div>
                    <div class="input-group">
                        <label for="expiry-date">Expiry Date</label>
                        <input type="text" id="expiry-date" name="expiry-date" placeholder="MM/YY" required>
                    </div>
                    <div class="input-group">
                        <label for="cvv">CVV</label>
                        <input type="text" id="cvv" name="cvv" placeholder="123" required>
                    </div>
                </form>
            </div>

            <!-- PayPal Form -->
            <div class="paypal-form" style="display: none;">
                <h3>Pay with PayPal</h3>
                <p>You will be redirected to PayPal to complete your payment.</p>
                <div class="input-group">
                    <label for="paypal-email">PayPal Email</label>
                    <input type="email" id="paypal-email" name="paypal-email" placeholder="Enter your PayPal email" required>
                </div>
                <div class="input-group">
                    <label for="paypal-amount">Amount</label>
                    <input type="text" id="paypal-amount" name="paypal-amount" placeholder="Enter amount" required>
                </div>
            </div>

            <!-- Bank Transfer Form -->
            <div class="bank-transfer-form" style="display: none;">
                <h3>Bank Transfer Details</h3>
                <p>Transfer the payment to the following account:</p>
                <p>Account Number: 1234567890</p>
                <p>Bank: ABC Bank</p>
                <div class="input-group">
                    <label for="bank-name">Your Bank Name</label>
                    <input type="text" id="bank-name" name="bank-name" placeholder="Enter your bank name" required>
                </div>
                <div class="input-group">
                    <label for="bank-amount">Amount</label>
                    <input type="text" id="bank-amount" name="bank-amount" placeholder="Enter amount" required>
                </div>
            </div>

            <button class="submit-btn">Proceed to Payment</button>
        </div>
    </section>

    <script src="payment.js?v=2"></script>
</body>
</html>
