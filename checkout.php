<?php
session_start();
include("core/dbConnect.php");
include("core/functions.php");

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

$id = isset($_GET['id']) ? $_GET['id'] : null;
$act = isset($_GET['act']) ? $_GET['act'] : null;
$flag = isset($_GET['flag']) ? $_GET['flag'] : null;

if ($act) {
    if ($act === 'add' && $id) {
        $_SESSION['sess_cart'][$id] = ($_SESSION['sess_cart'][$id] ?? 0) + 1;
    } elseif ($act === 'del' && $id) {
        $_SESSION['sess_cart'][$id] = max(0, ($_SESSION['sess_cart'][$id] ?? 0) - 1);
        if ($_SESSION['sess_cart'][$id] === 0) {
            unset($_SESSION['sess_cart'][$id]);
        }
    } elseif ($act === 'payment' && $flag === 'pay') {
        if (isset($_SESSION['user_id'])) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM `order` WHERE order_user_id = :user_id AND order_stat != 'Completed'");
                $stmt->execute(['user_id' => $user_id]);

                if ($stmt->rowCount() > 0) {
                    unset($_SESSION['sess_cart']);
                    header('Location: checkout.php?act=error');
                    exit();
                }

                foreach ($_SESSION['sess_cart'] as $key => $quantity) {
                    $serv_id = decryptIt($key);
                    $stmt = $pdo->prepare("SELECT * FROM service WHERE serv_id = :serv_id");
                    $stmt->execute(['serv_id' => $serv_id]);
                    $product = $stmt->fetch(PDO::FETCH_ASSOC);

                    $total_amount = $product['serv_prc'] * $quantity;
                    $date = date('Y-m-d H:i:s');

                    $stmt = $pdo->prepare("INSERT INTO `order` (order_user_id, order_serv_id, order_qty, order_dte, order_stat) 
                                           VALUES (:user_id, :serv_id, :quantity, :date, 'Preparing')");
                    $stmt->execute([
                        'user_id' => $user_id,
                        'serv_id' => $serv_id,
                        'quantity' => $quantity,
                        'date' => $date
                    ]);

                    $inv_order_id = $pdo->lastInsertId();
                    $payment_type = ($_GET['return'] === 'paypal') ? 'paypal' : 'cash';
                    $stmt = $pdo->prepare("INSERT INTO invoice (inv_order_id, inv_pay_stat, inv_amt, inv_type, inv_dte) 
                                           VALUES (:inv_order_id, 'paid', :total_amount, :payment_type, :date)");
                    $stmt->execute([
                        'inv_order_id' => $inv_order_id,
                        'total_amount' => $total_amount,
                        'payment_type' => $payment_type,
                        'date' => $date
                    ]);
                }
                unset($_SESSION['sess_cart']);
                header('Location: checkout.php?act=success');
                exit();
            } catch (PDOException $e) {
                die('Database Error: ' . $e->getMessage());
            }
        } else {
            header('Location: checkout.php?act=not_login');
            exit();
        }
    } else {
        header('Location: checkout.php?act=cancel');
        exit();
    }
    header('Location: checkout.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Check Out</title>
    <?php require("pages/header.php"); ?>
    <link rel="canonical" href="checkout.php">
    <link rel="stylesheet" href="blog.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="bootstrap/css/all.min.css">
    <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
    <script src="bootstrap/js/jquery-3.4.1.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container">
        <?php include("pages/navbar.php"); ?>
        <div class="wrapper" style="overflow-y:auto;overflow-x:hidden">
            <!-- Modal Login -->
            <div class="modal fade" id="modalLoginForm" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <form action="action.php" method="post" id="login_form">
                            <div class="modal-header text-center">
                                <h4 class="modal-title w-100 font-weight-bold">Hello.</h4>
                            </div>
                            <div class="modal-body mx-3">
                                <div class="row">
                                    <div class="col">
                                        <label class="sr-only" for="inlineFormInputGroup1"></label>
                                        <div class="input-group mb-2">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text"><i class="fas fa-user"></i></div>
                                            </div>
                                            <input type="text" class="form-control" name="username" placeholder="Username">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <label class="sr-only" for="inlineFormInputGroup2"></label>
                                        <div class="input-group mb-2">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text"><i class="fas fa-lock"></i></div>
                                            </div>
                                            <input type="password" class="form-control" name="password" placeholder="Password">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <small class="text-muted text-center"> Don't have an account? <a href="" class="text-primary" data-toggle="modal" data-dismiss="modal" data-target="#modalSignupForm">Sign up</a> now!</small><br>
                                        <small class="text-muted text-center"> Become a vendor, register <a href="" class="text-primary" data-toggle="modal" data-dismiss="modal" data-target="#modalVendorForm">here</a>.</small>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer d-flex justify-content-center">
                                <div class="col text-center">
                                    <input type="hidden" name="login">
                                    <input type="submit" class="btn btn-default" value="Login">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <style>.modal-backdrop {display:none!important}</style>
        <div class="my-5">
            <h2 class="display-4"><i class="fas fa-shopping-basket"></i> Checkout Basket</h2>
            <table id="cart" class="table table-hover table-condensed">
                <tr>
                    <th style="width:60%">Item Info</th>
                    <th style="width:10%">Price</th>
                    <th style="width:15%">Quantity</th>
                    <th style="width:15%">Subtotal</th>
                </tr>
                <?php
                $tot_prc = 0;
                if (!empty($_SESSION['sess_cart'])) {
                    foreach ($_SESSION['sess_cart'] as $key => $data) {
                        $serv_id = decryptIt($key);

                        try {
                            // PDO query to get service details
                            $stmt = $pdo->prepare("SELECT * FROM service WHERE serv_id = :serv_id");
                            $stmt->execute(['serv_id' => $serv_id]);
                            $row = $stmt->fetch(PDO::FETCH_ASSOC);
                            $subtotal = $row['serv_prc'] * $data;
                            $tot_prc += $subtotal;
                        } catch (PDOException $e) {
                            die('Database Error: ' . $e->getMessage());
                        }
                        ?>
                        <tr>
                            <td>
                                <div class="row">
                                    <div class="col-3 hidden-xs"><img src="<?= $row['serv_img'] ? "img/menu/{$row['serv_img']}" : 'http://placehold.it/100x100' ?>" alt="Product Image" class="img-fluid" /></div>
                                    <div class="col">
                                        <h4 class="text-capitalize"><?= $row['serv_name'] ?></h4>
                                        <p><?= $row['serv_desc'] ?></p>
                                    </div>
                                </div>
                            </td>
                            <td><?= number_format($row['serv_prc'], 2) ?></td>
                            <td>
                                <ul class="pagination">
                                    <li class="page-item"><a class="page-link" href="checkout.php?act=del&id=<?= $key ?>">-</a></li>
                                    <li class="page-item disabled"><a class="page-link" href="#"><?= $data ?></a></li>
                                    <li class="page-item"><a class="page-link" href="checkout.php?act=add&id=<?= $key ?>">+</a></li>
                                </ul>
                            </td>
                            <td><?= number_format($subtotal, 2) ?></td>
                        </tr>
                    <?php
                    }
                } else {
                    echo '<tr><td colspan="5"><p>Nothing yet in your basket :( <br><small> Order some art now!</small></p></td></tr>';
                }
                ?>
                <tr>
                    <td colspan="2" class="hidden-xs"></td>
                    <td>
                        <span>Subtotal</span><br>
                        <span>Service Charge</span><br>
                        <span>Total</span>
                    </td>
                    <td>
                        <span><?= isset($tot_prc) ? number_format($tot_prc, 2) : 0 ?></span><br>
                        <span><?= isset($tot_prc) ? number_format($tot_prc * 0.1, 2) : 0 ?></span><br>
                        <h4 class="text-success"> USD $<?= isset($tot_prc) ? number_format($tot_prc + ($tot_prc * 0.1), 2) : 0 ?></h4>
                    </td>
                </tr>
                <?php if (isset($_SESSION['sess_cart'])) { ?>
                <tr>
                    <td colspan="2" class="hidden-xs"></td>
                    <td colspan="2">
                        <div class="form-group">
                            <label><input type="radio" class="form-input" name="payment" value="cash" checked> <i class="fas fa-wallet"></i> Cash</label>
                            <label><input type="radio" class="form-input" name="payment" value="paypal"> <i class="fab fa-cc-paypal"></i> Paypal</label>
                        </div>
                        <div id="cash">
                            <form action="checkout.php" method="get">
                                <input type="hidden" name="flag" value="pay">
                                <input type="hidden" name="return" value="cash">
                                <input type="hidden" name="act" value="payment">
                                <button type="submit" class="btn btn-success btn-block">Confirm Order <i class="fa fa-angle-right"></i></button>
                            </form>
                        </div>
                        <div id="paypal" style="display:none;">
                            <form action="<?= PAYPAL_URL ?>" method="post">
                                <input type="hidden" name="business" value="<?= PAYPAL_ID ?>">
                                <input type="hidden" name="cmd" value="_xclick">
                                <input type="hidden" name="amount" value="<?= number_format($tot_prc, 2) ?>">
                                <input type="hidden" name="currency_code" value="<?= PAYPAL_CURRENCY ?>">
                                <input type="hidden" name="return" value="<?= PAYPAL_RETURN_URL ?>">
                                <input type="hidden" name="cancel_return" value="<?= PAYPAL_CANCEL_URL ?>">
                                <input type="submit" class="btn btn-primary btn-block" value="Pay Now">
                            </form>
                        </div>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $("input[name='payment']").change(function() {
                var payment_type = $("input[name='payment']:checked").val();
                $("#cash").toggle(payment_type == "cash");
                $("#paypal").toggle(payment_type == "paypal");
            });

            if (!$("input[name='payment']:checked").val()) {
                $("input[name='payment'][value='cash']").prop('checked', true);
                $("#cash").show();
                $("#paypal").hide();
            }
        });
    </script>
    <script src="bootstrap/js/app.js"></script>
</body>
</html>
