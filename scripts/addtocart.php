<?php
    $login = parse_ini_file('../userdata/config.ini', true)['store'];
    $db = new mysqli($login['server'], $login['username'], $login['password'], $login['database']);
    if ($db->connect_errno) {
        echo "<h1>Moorhead Bicycle</h1>";
        exit('Something went wrong. Please try again later.');
    }
    
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }
    
    if (isset($_POST['clothing']) || isset($_POST['accessory']) || isset($_POST['bicycle'])) {
        $quantity = htmlspecialchars(trim($_POST['quantity']));
        
        echo "$quantity&emsp;";
        
        if (isset($_POST['bicycle'])) {    
            $product = htmlspecialchars(trim($_POST['bicycle']));
        } else if (isset($_POST['clothing'])) {
            $product = htmlspecialchars(trim($_POST['clothing']));
        } else {
            $product = htmlspecialchars(trim($_POST['accessory']));
        }
        
        $prod_confirm = "SELECT sku, name, price FROM PRODUCT WHERE sku = ?";
        $stmt = $db->prepare($prod_confirm);
        $stmt->bind_param("s", $product);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($sku, $name, $price);
            
        if ($stmt->num_rows <= 0) {
            echo "Something went wrong. We could not find that product.";
            exit();
        }
        
        if (isset($_POST['clothing']) && $_POST['clothing'] !== "") {
            $size = htmlspecialchars(trim($_POST['size']));
            switch ($size) {
                case 'S':
                    $sizenum = 1;
                    break;
                case 'M':
                    $sizenum = 2;
                    break;
                case 'L':
                    $sizenum = 3;
                    break;
                case 'X':
                    $sizenum = 4;
                    break;
            }
            
            if ($stmt->fetch()) {
                if (!empty($_SESSION['cart'][$sku])) {
                    echo "$name is already in your cart.<br><br>";
                } else {
                    $_SESSION['cart'][$sku.",".$size] = array(
                        "category"=>"Clothing",
                        "name"=>$name,
                        "size"=>$sizenum,
                        "quantity"=>$quantity,
                        "price"=>$price
                    );
                       
                    echo "$name $size added to cart.<br><br>";
                }
            }
        }
        
        if (isset($_POST['accessory']) && $_POST['accessory'] !== "") {
            if ($stmt->fetch()) {
                if (!empty($_SESSION['cart'][$sku])) {
                    echo "$name is already in your cart.<br><br>";
                } else {
                    $_SESSION['cart'][$sku] = array(
                        "category"=> "Accessory",
                        "name"=>$name,
                        "quantity"=>$quantity,
                        "price"=>$price
                    );
                    
                    echo "$name added to cart.<br><br>";
                }
            }
        }
    
        // Begin adding bicycle to cart 
        if (isset($_POST['bicycle']) && $_POST['bicycle'] !== "") {
            if ($_POST['quantity'] <= 0) {
                echo "Something went wrong. Please enter a valid quantity.";
            }
            
            $color = htmlspecialchars(trim($_POST['color']));
            $color_confirm = "SELECT color FROM BICYCLECOLOR WHERE sku = ?";
            $cstmt = $db->prepare($color_confirm);
            $cstmt->bind_param("s", $product);
            $cstmt->execute();
            $cstmt->store_result();
            $cstmt->bind_result($c);
            
            if ($cstmt->num_rows <= 0) {
                echo "Something went wrong. That color is not available for that product.<br>";
                exit();
            }
            
            $size = htmlspecialchars(trim($_POST['size']));
            $size_confirm = "SELECT size FROM BICYCLESIZE WHERE sku = ?";
            $sstmt = $db->prepare($size_confirm);
            $sstmt->bind_param("s", $product);
            $sstmt->execute();
            $sstmt->store_result();
            
            if ($sstmt->num_rows <= 0) {
                echo "Something went wrong. That size is not available for that product.";
                exit();
            }
            
            if ($stmt->fetch()) {
                if (!empty($_SESSION['cart'][$sku])) {
                    echo "$name is already in your cart.<br><br>";
                } else {
                    $_SESSION['cart'][$sku.",".$color.",".$size] = array(
                        "category"=>"Bicycle",
                        "name"=>$name,
                        "color"=>$color,
                        "size"=>$size,
                        "quantity"=>$quantity,
                        "price"=>$price
                    );
                       
                    echo "$name $color $size added to cart.<br><br>";
                }
            }
                
                $cstmt->free_result();
                $cstmt->close();
                
                $sstmt->free_result();
                $sstmt->close();
        }
            
    echo "Current cart total: $";
    $total = 0;
    
    foreach($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
            
    echo number_format($total, 2)."<br><br>";
    
    $stmt->free_result();
    $stmt->close();
    
    }
    
    $db->close();
?>
