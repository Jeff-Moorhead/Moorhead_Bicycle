<?php
    session_start();
    require_once('../scripts/login_check.php');
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Moorhead Bicycle - Cart</title>
        <link rel="stylesheet" href="styles/tables.css">
    </head>
    <body>
        <h1>Moorhead Bicycle - Cart</h1>
        <a href="index.php">Home</a><br><br>
        <?php
            if (isset($_POST['remove'])) {
                $toremove = htmlspecialchars($_POST['remove']);
                
                if (isset($_SESSION['cart']) && array_key_exists($toremove, $_SESSION['cart'])) {
                    echo $_SESSION['cart'][$toremove]['name']." has been removed from your cart.<br><br>";
                    unset($_SESSION['cart'][$toremove]);
                } else {
                    echo "Something went wrong. That item is not in your cart.<br><br>";
                }
            }
            if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                $carttotal = 0; ?>
                <table>
                    <tr class="prodheader">
                        <td>Quantity</td>
                        <td>Product</td>
                        <td>Item Total</td>
                        <td></td>
                    </tr>
                    <?php
                        foreach($_SESSION['cart'] as $item=>$value) { ?>
                        <tr>
                            <form action="<?php echo htmlspecialchars($_POST['PHP_SELF']);?>" method="post">
                                <input type="hidden" name="remove" value="<?php echo $item;?>">
                            <?php
                                $itemtotal = $value['quantity'] * $value['price'];
                                $carttotal += $itemtotal;
                                echo "<td>".$value['quantity']."</td><td>".$value['name'];
                                echo (isset($value['color']) ? "  ".$value['color'] : "");
                                if (isset($value['size'])) {
                                    switch ($value['size']) {
                                        case 1:
                                            $size = ' S';
                                            break;
                                        case 2:
                                            $size = ' M';
                                            break;
                                        case 3:
                                            $size = ' L';
                                            break;
                                        case 4:
                                            $size = ' XL';
                                            break;
                                        default:
                                            $size = $value['size'];
                                            break;
                                    }
                                } else {
                                    $size = "";
                                }
                                echo $size;
                                echo "</td><td>$".$itemtotal."</td><td>";?>
                                <input type="submit"value="Remove from Cart"></td>
                            </form>
                        </tr>
                <?php
                    } 
                ?>
                </table>
        <?php
                echo "Current cart total: $".number_format($carttotal, 2)."<br><br>";
        ?>
        <form action="action.php" method="post">
                    <input type="hidden" name="i_action" value="checkout">
                    <input type="submit" value="Checkout">
        </form>
        <?php
            } else {
                echo "Your cart is currently empty.";
            }
        ?>
    </body>
</html>