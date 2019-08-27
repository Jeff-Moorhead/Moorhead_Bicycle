<?php
    if (!isset($_SESSION)) {
        session_start();
    }
    require_once("../scripts/login_check.php");
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Moorhead Bicycle - Store</title>
        <link rel="stylesheet" href="styles/tables.css">
    </head>
    <body>
        <h1>Moorhead Bicycle - Welcome <?php echo $_SESSION['username'];?>!</h1>
        
        <?php require_once("../scripts/addtocart.php"); ?>
        <a href="logouthandler.php">Logout</a>&emsp;
        
        <a href="carthandler.php">View Cart</a>&emsp;
        
        <a href="viewordershandler.php">View Your Orders</a>
        <br><br>
        
        <form action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]);?>" method="get">
            Search by Product Type: <select name="prodtype">
                <option value="bicycle">Bicycles</option>
                <option value="clothing">Clothing</option>
                <option value="accessory">Accessories</option>
            </select>
            <input type="submit" value="Search">
        </form>
        
        <?php 
            if (isset($_GET['prodtype'])) {
                $prodtype = trim($_GET['prodtype']);
            }
            
            $login = parse_ini_file('../userdata/config.ini', true)['store'];
                
            $db = new mysqli($login['server'], $login['username'], $login['password'], $login['database']);
                            
            if ($db->connect_errno) {
                exit("Something went wrong. Please try again later.");
            }

            switch ($prodtype):  # Display product data based on product type (bicycle, clothing, or accessory)
                
# -------------------------- Bicycles ------------------------------------
                case "bicycle":
                    ?>
                    <table>
                        <tr class="prodheader">
                            <td>Product</td>
                            <td>Description</td>
                            <td>Style</td>
                            <td>Manufacturer</td>
                            <td>Model</td>
                            <td>Groupset</td>
                            <td>Size</td>
                            <td>Color</td>
                            <td>Warranty</td>
                            <td>Price</td>
                            <td>Quantity</td>
                        </tr>
                        <?php
                            $prodquery = "SELECT b.sku, p.name, p.description, b.style, p.manufacturer, p.model, b.groupset, p.warranty, p.price FROM PRODUCT AS p, BICYCLE AS b WHERE p.sku = b.sku ORDER BY b.sku";
            
                            if ($products = $db->query($prodquery)) {
                                while ($currprod = $products->fetch_assoc()) {
                                    $sizequery = "SELECT s.size FROM BICYCLESIZE AS s WHERE s.sku = '".$currprod['sku']."'";
                                    
                                    $colorquery = "SELECT c.color FROM BICYCLECOLOR AS c WHERE c.sku = '".$currprod['sku']."'";
                                    
                                    ?>
                                                
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
                                        <input type="hidden" name="bicycle" value="<?php echo $currprod['sku'];?>">
                                        <tr>
                                            <td><?php echo $currprod['name'];?></td>
                                            <td><?php echo $currprod['description'];?></td>
                                            <td><?php echo $currprod['style'];?></td>
                                            <td><?php echo $currprod['manufacturer'];?></td>
                                            <td><?php echo $currprod['model'];?></td>
                                            <td><?php echo $currprod['groupset'];?></td>
                                            <td><select name="size">
                                            <?php
                                                if ($sizes = $db->query($sizequery)) {
                                                    while ($currsize = $sizes->fetch_assoc()) {
                                                        echo "<option value=".$currsize['size'].">".$currsize['size']."</option>";
                                                    }
                                                }
                                            ?>
                                            </select></td>
                                            <td><select name="color">
                                            <?php
                                                if ($colors = $db->query($colorquery)) {
                                                    while ($currcolor = $colors->fetch_assoc()) {
                                                        echo "<option value=".$currcolor['color'].">".$currcolor['color']."</option>";
                                                    }
                                                }
                                            ?>
                                            </select></td>
                                            <td><?php echo $currprod['warranty']." years";?></td>
                                            <td><?php echo "$".number_format($currprod['price'], 2);?></td>
                                            <td><input type="number" min="1" max="99" name="quantity" style="width: 3em" required></td>
                                            <td><input type="submit" value="Add to Cart"></td>
                                    </form>
                        <?php
                                }
                            }
                        ?>
                    </table>
                    <?php
                        break;

# --------------------- Clothing ---------------------------            
                case "clothing":
                    ?>
                    <table>
                        <tr class="prodheader">
                            <td>Product</td>
                            <td>Description</td>
                            <td>Gender</td>
                            <td>Manufacturer</td>
                            <td>Size</td>
                            <td>Price</td>
                            <td>Quantity</td>
                        </tr>
                <?php
                    $prodquery = "SELECT c.sku, p.name, p.description, c.gender, p.manufacturer, p.price FROM PRODUCT AS p, CLOTHING AS c WHERE p.sku = c.sku ORDER BY c.sku";
                    
                    if ($products = $db->query($prodquery)) {
                        while ($currprod = $products->fetch_assoc()) {
                            ?>
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
                                <input type="hidden" name="clothing" value="<?php echo $currprod['sku'];?>">
                                <tr>
                                    <td><?php echo $currprod['name'];?></td>
                                    <td><?php echo $currprod['description'];?></td>
                                    <td><?php echo $currprod['gender'];?></td>
                                    <td><?php echo $currprod['manufacturer'];?></td>
                                    <!-- All clothing items are available in S, M, L, and XL. This does not need to be stored in the database -->
                                    <td><select name="size">
                                        <option value="S">Small</option>
                                        <option value="M">Medium</option>
                                        <option value="L">Large</option>
                                        <option value="X">XLarge</option>
                                    </select></td>
                                    <td><?php echo "$".number_format($currprod['price'], 2);?></td>
                                    <td><input type="number" name="quantity" min="1" max="99" style="width: 3em" required></td>
                                    <td><input type="submit" value="Add to Cart"></td>
                                </tr>
                            </form>

                    <?php
                        }
                    }
                    ?>
                    </table>
                    <?
                    break;

# -------------------- Accessory ---------------------------  
                case "accessory":
                    ?>
                    <table>
                        <tr class="prodheader">
                            <td>Product</td>
                            <td>Description</td>
                            <td>Category</td>
                            <td>Manufacturer</td>
                            <td>Price</td>
                            <td>Quantity</td>
                        </tr>
                <?php
                    $prodquery = "SELECT a.sku, p.name, p.description, a.category, p.manufacturer, p.price FROM PRODUCT AS p, ACCESSORY AS a WHERE p.sku = a.sku ORDER BY a.sku";
                    if ($products = $db->query($prodquery)) {
                        while ($currprod = $products->fetch_assoc()) {
                            ?>
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
                                <input type="hidden" name="accessory" value="<?php echo $currprod['sku'];?>">
                                <tr>
                                    <td><?php echo $currprod['name'];?></td>
                                    <td><?php echo $currprod['description'];?></td>
                                    <td><?php echo $currprod['category'];?></td>
                                    <td><?php echo $currprod['manufacturer'];?></td>
                                    <td><?php echo "$".number_format($currprod['price'], 2);?></td>
                                    <td><input type="number" name="quantity" min="1" max="99" style="width: 3em" required></td>
                                    <td><input type="submit" value="Add to Cart"></td>
                                </tr>
                            </form>
                <?php
                        }
                    }
                ?>
                    </table>
        <?php
            endswitch;
            $db->close();
        ?>
    </body>
</html>