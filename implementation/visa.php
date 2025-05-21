<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credit Card Payment</title>
    <link rel="stylesheet" href="payment.css">
</head>
<body>

    <section class="payment-method" id="payment-method">
        <div class="container">
            <h1 class="heading">Credit Card Payment</h1>
            
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

            <button class="submit-btn">Proceed to Payment</button>
        </div>
    </section>

</body>
</html>
