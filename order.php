<?php
session_start();
require_once "core/dbConnect.php";
require_once "core/functions.php";

// Ensure the user is logged in
if (!isUserLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION["user_id"];
$status = $_SESSION["user_status"];

// Redirect based on user status
if ($status === "admin") {
    header('Location: admin/pnl_user');
    exit();
} elseif ($status === "shop") {
    header('Location: shop/pnl_order');
    exit();
}

// Handle payment processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = filter($_POST['payment_method']);

    if ($payment_method === 'stripe') {
        // Process Stripe payment
        // Include Stripe PHP library
        require_once('vendor/autoload.php');
        \Stripe\Stripe::setApiKey('YOUR_STRIPE_SECRET_KEY');

        // Create a new charge
        try {
            $charge = \Stripe\Charge::create([
                'amount' => (int)$_POST['amount'] * 100, // Amount in cents
                'currency' => 'usd',
                'description' => 'Order Payment',
                'source' => $_POST['stripeToken'],
            ]);

            // Update order status to paid
            $stmt = $connect->prepare("UPDATE `order` SET order_stat = 'Paid' WHERE order_user_id = ? AND order_stat != 'Completed'");
            $stmt->execute([$user_id]);

            header('Location: order.php?payment=success');
            exit();
        } catch (\Stripe\Exception\CardException $e) {
            // Payment failed
            header('Location: order.php?payment=failed');
            exit();
        }
    } elseif ($payment_method === 'paypal') {
        // Redirect to PayPal payment page
        // PayPal integration code here...
    } elseif ($payment_method === 'cash') {
        // Process Cash payment (update order status)
        $stmt = $connect->prepare("UPDATE `order` SET order_stat = 'Pending Payment' WHERE order_user_id = ? AND order_stat != 'Completed'");
        $stmt->execute([$user_id]);

        header('Location: order.php?payment=success');
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="groceries.ico">
    <title>Shop It Up Store - Order Status</title>
    <link rel="stylesheet" href="bootstrap/css/all.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <script src="bootstrap/js/jquery-3.4.1.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body class="content bg-white">
    <?php include("pages/navbar.php"); ?>
    <div class="container my-5">
        <div id="alert" style="position:absolute;z-index:1;"></div>
        <h2 class="display-4"><i class="fas fa-tasks"></i> Order Status</h2>
        <div class="row px-3">
            <div class="col">
                <div class="row border-top py-3 font-weight-bold">
                    <div class="col-6">Item Info</div>
                    <div class="col-2">Price</div>
                    <div class="col-2">Quantity</div>
                    <div class="col-2">Status</div>
                </div>

                <?php
                if (isset($user_id)) {
                    $stmt = $connect->prepare("SELECT o.*, i.inv_type, c.ctlog_nme, c.ctlog_prc FROM `order` o 
                                              JOIN invoice i ON o.order_id = i.inv_order_id
                                              JOIN catalog c ON o.order_catalog_id = c.ctlog_id
                                              WHERE o.order_user_id = ? AND o.order_stat != 'Completed'");
                    $stmt->execute([$user_id]);
                    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $total_price = 0;

                    if (count($orders) > 0) {
                        foreach ($orders as $order) {
                            $subtotal = $order['ctlog_prc'] * $order['order_qty'];
                            $total_price += $subtotal;

                            echo '<div class="row border-top py-3">';
                            echo '<div class="col-6 text-capitalize">' . htmlspecialchars($order['ctlog_nme']) . '</div>';
                            echo '<div class="col-2">₹ ' . number_format($order['ctlog_prc'], 2) . '</div>';
                            echo '<div class="col-2">(' . $order['order_qty'] . ')x Order</div>';
                            echo '<div class="col-2">' . htmlspecialchars($order['order_stat'] ?: 'Preparing') . '</div>';
                            echo '</div>';
                        }

                        $total_service_charge = number_format(0.10 * $total_price, 2, '.', '');
                        $grand_total = number_format($total_price + $total_service_charge, 2, '.', '');

                        echo '<div class="row border-top pt-3">';
                        $payment_status = ($order['inv_type'] === 'paypal') ? 'Paid' : 'Unpaid';
                        echo '<h4 class="col text-center text-' . ($payment_status === 'Paid' ? 'primary' : 'success') . '">₹ ' . $grand_total . ' (' . $payment_status . ')</h4>';
                        echo '</div>';

                        echo '<div class="row">';
                        echo '<p class="col text-center">Pay by ' . ucfirst($order['inv_type']) . '.</p>';
                        echo '</div>';

                        echo '<div class="row pb-5">';
                        echo '<small class="col text-center text-muted">Note: Please have small change ready and follow the exact total price.</small>';
                        echo '</div>';
                    } else {
                        echo '<div class="row border-top py-3">';
                        echo '<div class="col">No items have been ordered.</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="row border-top py-3">';
                    echo '<div class="col">No items have been ordered.</div>';
                    echo '</div>';
                }
                ?>

                <!-- Payment Form -->
                <div class="row border-top py-3">
                    <div class="col">
                        <h4>Select Payment Method</h4>
                        <form action="order.php" method="post" id="payment-form">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="paymentStripe" value="stripe" checked>
                                <label class="form-check-label" for="paymentStripe">Stripe</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="paymentPaypal" value="paypal">
                                <label class="form-check-label" for="paymentPaypal">PayPal</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="paymentCash" value="cash">
                                <label class="form-check-label" for="paymentCash">Cash</label>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Proceed with Payment</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        var stripe = Stripe('YOUR_STRIPE_PUBLIC_KEY');
        var elements = stripe.elements();
        var style = {
            base: {
                color: '#32325d',
                lineHeight: '24px',
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                    color: '#aab7c4'
                }
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a'
            }
        };
        var card = elements.create('card', {style: style});
        card.mount('#card-element');
        card.addEventListener('change', function(event) {
            var displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });
    </script>
    <script src="bootstrap/js/app.js"></script>
</body>
</html>
