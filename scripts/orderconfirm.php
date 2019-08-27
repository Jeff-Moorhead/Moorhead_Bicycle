<?php
    session_start();
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Order Confirmed</title>
    </head>
    <body>
        <h1>Moorhead Bicycle - Order Confirmed</h1>
        <?php
            if (isset($_POST)) {
                $total = htmlspecialchars($_POST['total']);
                $name = htmlspecialchars(trim($_POST['name']));
                $addr1 = htmlspecialchars(trim($_POST['addr1']));
                $addr2 = htmlspecialchars(trim($_POST['addr2']));
                $city = htmlspecialchars(trim($_POST['city']));
                $state = htmlspecialchars(trim($_POST['state']));
                $country = htmlspecialchars(trim($_POST['country']));
                $zip = htmlspecialchars(trim($_POST['zip']));
                
                $orderdate = date("Y-m-d");
                $estimated_delivery  = new DateTime(date("Y-m-d"));
                $estimated_delivery->modify("+10 days");
                $estimated_delivery = $estimated_delivery->format("Y-m-d");
                
                if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
                    $login = parse_ini_file('../userdata/config.ini', true)['orders'];
                    $db = new mysqli($login['server'], $login['username'], $login['password'], $login['database']);
                    if ($db->connect_errno) {
                        echo "<h1>Moorhead Bicycle</h1>";
                        exit('Something went wrong. Please try again later.');
                    }
                    
                    $createorder = "INSERT INTO ORDERHDR (username, recipient, addr1, addr2, city, state, country, zip, estdelivery, orderdate, ordertotal) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    if (($insert = $db->prepare($createorder)) !== FALSE) {
                        $insert->bind_param("sssssssssss", $_SESSION['username'], $name, $addr1, $addr2, $city, $state, $country, $zip, $estimated_delivery, $orderdate, $total);
                    
                        if ($insert->execute()) {
                            $insert->close();
                        } else {
                            exit("Something went wrong. ".$insert->errno.": ".$insert->error);
                        }
                        
                        $selectorder = "SELECT MAX(ordernum) AS currorder FROM ORDERHDR WHERE username = '".$_SESSION['username']."' AND orderdate = '".$orderdate."'";
                        $currentorder = $db->query($selectorder);
                        
                        if ($currentorder->num_rows > 1) {
                            exit('Something went wrong. Please try again.');
                        }
                        
                        $result = $currentorder->fetch_assoc();
                        $ordernum = $result['currorder'];
                        
                        foreach ($_SESSION['cart'] as $k => $v) {  # $k is the product's sku + color + size where applicable
                            $tmp = explode(',', $k);
                            $k = $tmp[0];  # Extract the product sku from the full cart array key
                            
                            $inserttrailer = "INSERT INTO ORDERTRL VALUES (?, ?, ?, ?, ?)";
                            if (($createtrailer = $db->prepare($inserttrailer)) !== FALSE) {
                                if (!($createtrailer->bind_param("isisi", $ordernum, $k, $v['size'], $v['color'], $v['quantity']))) {
                                    exit('Something went wrong. '.$createtrailer->errno.': '.$createtrailer->err);
                                }
                                
                                if (!($createtrailer->execute())) {
                                    exit('Something went wrong. '.$createtrailer->errno.': '.$createtrailer->error);
                                }
                                
                                $createtrailer->close();
                            }
                        }
                        
                        $currentorder->close();
                        
                    } else {
                        exit('Somehting went wrong. '.$db->errno.': '.$db->error);
                    }
                    
                    $db->close();
                    
                }
                
                echo "Thank you! Your order has been confirmed. See details below.<br><br>";
                echo "Order number: $ordernum<br>";
                echo "Order total: $".number_format($total, 2)."<br>";
                
                $tmp = strtotime($estimated_delivery);
                $deldate = date("d M, Y", $tmp);  # Variable only used for output, $estimated_delivery is stored in the database.
                
                echo "Estimated delivery date: ".$deldate."<br><br>";
                echo "<b>Shipping Information:</b><br>";
                echo "$name<br>$addr1<br>";
                if (!empty($addr2)) {
                    echo $addr2."<br>";
                }
                echo "$city<br>$state<br>$country<br>$zip<br><br>";
                echo "Thank you for your order!";
                unset($_SESSION['cart']);
                unset($_POST);
            }
        ?>
        <a href="index.php">Return to Home</a>
    </body>
</html>