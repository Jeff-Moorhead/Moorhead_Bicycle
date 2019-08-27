<?php
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Moorhead Bicycle - Orders</title>
        <link rel="stylesheet" href="styles/tables.css">
    </head>
    <?php
        $login = parse_ini_file('../userdata/config.ini', true)['orders'];
        $db = new mysqli($login['server'], $login['username'], $login['password'], $login['database']);
        if ($db->connect_errno) {
            echo "<h1>Moorhead Bicycle</h1>";
            exit('Something went wrong. Please try again later.');
        }
    ?>
    <body>
        <h1>Moorhead Bicycle - Your Orders</h1>
        <a href="index.php">Home</a><br><br>
            <?php
                if (isset($_POST['cancel'])) {
                    $canceledorder = htmlspecialchars($_POST['cancel']);
                    
                    $deleteorderhdr = "DELETE FROM ORDERHDR WHERE ordernum = ?";
                    if (!($hdrstmt = $db->prepare($deleteorderhdr))) {
                        echo "Something went wrong. ".$db->errno.": ".$db->error;
                        exit();
                    }
                    
                    if (!($hdrstmt->bind_param("i", $canceledorder))) {
                        echo "Something went wrong. ".$hdrstmt->errno.": ".$hdrstmt->error;
                        exit();
                    }
                    
                    if (!($hdrstmt->execute())) {
                        echo "Something went wrong. ".$hdrstmt->errno.": ".$hdrstmt->error;
                        exit();
                    }
                    
                    $deleteordertrl = "DELETE FROM ORDERTRL WHERE ordernum = ?";
                    if (!($trlstmt = $db->prepare($deleteordertrl))) {
                        echo "Something went wrong. ".$db->errno.": ".$db->error;
                        exit();
                    }
                    
                    if (!($trlstmt->bind_param("i", $canceledorder))) {
                        echo "Something went wrong. ".$trlstmt->errno.": ".$trlstmt->error;
                        exit();
                    }
                    
                    if (!($trlstmt->execute())) {
                        echo "Something went wrong. ".$trlstmt->errno.": ".$trlstmt->error;
                        exit();
                    }
                    
                    echo "Order #$canceledorder has been canceled.<br><br>";
                    $hdrstmt->close();
                    $trlstmt->close();
                }
            
                $selectorders = "SELECT * FROM ORDERS WHERE username = '".$_SESSION['username']."' ORDER BY ordernum";  # Select all orders for the current user
                if (!($results = $db->query($selectorders))) {  # Only fetch open orders after canceling any orders
                    echo 'Something went wrong. '.$db->errno.': '.$db->error;
                    exit();
                }
                
                $db->close();  # We will not need the database connection from once we have fetched the user's open orders
                
                if ($results->num_rows <= 0) {
                    echo "You do not have any open orders.";
                    exit();
                } else {
            ?>
            <table>
                <tr class="prodheader">
                    <td>Quantity</td>
                    <td>Product</td>
                    <td>Size</td>
                    <td>Color</td>
                    <td>Price (per unit)</td>
                    <td>Order Date</td>
                    <td>Estimated Delivery</td>
                </tr>
                
            <?php
                while ($row = $results->fetch_assoc()) {
                    if (isset($currorder) && $currorder !== $row['ordernum']) {
                        echo "<td><strong>Order Total: </strong>$".number_format($total, 2)."</td><tr><td>------------------------</tr>";
                    }
                    
                    if (!isset($currorder) || $currorder !== $row['ordernum']) {  # Only print the order number on the first row of the order. After that it is redundant.
                        $total = 0;
                    ?>
                        <tr>
                            <form action="<?php echo htmlspecialchars($_POST['PHP_SELF']);?>" method="post">
                                <input type="hidden" name="cancel" value="<?php echo $row['ordernum'];?>">
                            <?php    
                                echo "<td><strong>Order #:</strong> ".$row['ordernum']."</td>";
                            ?>
                            <td><input type="submit" value="Cancel Order"></td>
                            </form>
                        </tr>
                    <?php
                    }
                    
                    $total += ($row['quantity'] * $row['price']);
                    $currorder = $row['ordernum']; 

                    ?>
                    <tr>
                        <td>
                            <?php echo $row['quantity']; ?>
                        </td>
                        <td>
                            <?php echo $row['description']; ?>
                        </td>
                        <td>
                            <?php 
                                switch ($row['size']) {  # Convert numeric clothing sizes to user-friendly letter sizes. Values stored in the database are 1, 2, 3, or 4, corresponding to S, M, L, and XL
                                    case 1:
                                        $size = 'S';
                                        break;
                                    case 2:
                                        $size = "M";
                                        break;
                                    case 3:
                                        $size = "L";
                                        break;
                                    case 4:
                                        $size = "XL";
                                        break;
                                    default:
                                        $size = $row['size'];
                                    }
                                 
                                echo $size;
                            ?>
                        </td>
                        <td>
                            <?php echo (!empty($row['color']) ? $row['color'] : ''); ?>
                        </td>
                        <td>
                            <?php echo '$'.number_format($row['price'], 2); ?>
                        </td>
                        <td>
                            <?php $tmp = strtotime($row['orderdate']);
                                  $orderdate = date("d M, Y", $tmp);  # Variable only used for output, $estimated_delivery is stored in the database.
                                  echo $orderdate;
                            ?>
                        </td>
                        <td>
                            <?php $tmp = strtotime($row['estdelivery']);
                                  $estdel = date("d M, Y", $tmp);
                                  echo $estdel;
                            ?>
                        </td>
                    </tr>
            <?php
                    }
                    echo "<td><strong>Order Total: </strong>$".number_format($total, 2)."</td>";  # Print the order total for the last order. The last order total is never reached in the while loop.
                }
            ?>
        </table>
    </body>
</html>