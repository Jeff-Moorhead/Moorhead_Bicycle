<?php
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Thank you for Your Purchase!</title>
    </head>
    <body>
        <h1>Moorhead Bicycle - Order Confirmation</h1>
        <?php
            if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                $total = 0;
                foreach ($_SESSION['cart'] as $item) {
                    $total += $item['quantity'] * $item['price'];
                }
                echo "Your current order total is $".number_format($total, 2)."<br><br>";
            } 
        ?>
        <form action="action.php" method="post">
            <input type="hidden" name="i_action" value="orderconfirm">
            <input type="hidden" name="total" value="<?php echo $total;?>">
            <table>
                <tr>
                    <td>Recipient Name: </td>
                    <td><input type="text" name="name" required></td>
                </tr>
                <tr>
                    <td>Address 1: </td>
                    <td><input type="text" name="addr1" required></td>
                </tr>
                <tr>
                    <td>Address 2: </td>
                    <td><input type="text" name="addr2"></td>
                </tr>
                <tr>
                    <td>City: </td>
                    <td><input type="text" name="city" required></td>
                </tr>
                <tr>
                    <td>State: </td>
                    <td><input type="text" name="state" required></td>
                </tr>
                <tr>
                    <td>Country: </td>
                    <td><input type="text" name="country" required></td>
                </tr>
                <tr>
                    <td>Zip: </td>
                    <td><input type="text" name="zip" required></td>
                </tr>
                <tr>
                    <td><input type="submit" value="Confirm Order"></td>
                    <td><a href="carthandler.php">Return to Cart</a></td>
                </tr>
            </table>
        </form>
    </body>
</html>