<?php
    session_start();
    require_once("../scripts/employee_login_check.php");
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Moorhead Bicycle - Employees Only</title>
        <link href="styles/tables.css" rel="stylesheet">
    </head>
    <body>
        <h1>Moorhead Bicycle - Employee Portal</h1>
        <a href="logouthandler.php">Logout</a>&emsp;
        <a href="employeeinfohandler.php">View Your Employee Profile</a>&emsp;
        <a href="adminhandler.php">Admin Portal</a>
        <br><br>
        <?php
            if (isset($_GET['lname']) && isset($_GET['email'])) {
                $lastname = trim($_GET['lname']);
                $email = trim($_GET['email']);
                
                if (empty($lastname) || empty($email)) {
                    echo "Please enter all customer data.";
                    exit();
                }
                
                $login = parse_ini_file('../userdata/config.ini', true)['store'];
                            
                $db = new mysqli($login['server'], $login['username'], $login['password'], $login['database']);
                                        
                if ($db->connect_errno) {
                    exit("Something went wrong. Please try again later.");
                }
                
                # Only members of the sales department can view customer data.
                $checkemployee = "SELECT username FROM EMPLOYEE WHERE username = ? AND deptname = 'Sales'";
                if (!($checkstmt = $db->prepare($checkemployee))) {
                    echo "Something went wrong. ".$db->errno.": ".$db->error;
                    exit();
                }
                
                if (!($checkstmt->bind_param('s', $_SESSION['username']))) {
                    echo "Something went wrong. ".$checkstmt->errno.": ".$checkstmt->error;
                    exit();
                }
                
                if (!($checkstmt->execute())) {
                    echo "Something went wrong. ".$checkstmt->errno.": ".$checkstmt->error;
                    exit();
                }
                
                $employeesfound = $checkstmt->get_result();
                
                if ($employeesfound->num_rows < 1) {  # If the current user is not in the sales dept, exit.
                    echo "You do not have the proper permissions to view customer information.";
                    exit();
                }
                
                $selectcust = "SELECT * FROM CUSTOMERDATA WHERE lname = ? AND email = ?";
                if (!($stmt = $db->prepare($selectcust))) {
                    echo "Something went wrong. ".$db->errno.": ".$db->error;
                    exit();
                }
                
                if (!($stmt->bind_param("ss", $lastname, $email))) {
                    echo "Something went wrong. ".$stmt->errno.": ".$stmt->error;
                    exit();
                }
                
                if (!($stmt->execute())) {
                    echo "Something went wrong. ".$stmt->errno.": ".$stmt->error;
                    exit();
                }
                
                $custresult = $stmt->get_result();
                
                $stmt->close();
            }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="get">
            <table>
                <th>Customer Lookup</th>
                <tr><td>Last Name:</td><td><input name="lname" type="text" required></td></tr>
                <tr><td>Email:</td><td><input name="email" type="email" required></td></tr>
                <tr><td><input type="submit" value="Search"></td></tr>
            </table>
        </form>
        <?php
            if (isset($custresult)) {
                if ($custresult->num_rows === 0) {
                    echo "No users found.";
                    exit();
                }
                if ($custresult->num_rows > 1) {
                    echo "Something went wrong. Multiple users found.";
                    exit();
                }
        ?>
        <table>
            <tr class="prodheader">
                <td>First Name</td>
                <td>Last Name</td>
                <td>Username</td>
                <td>Email</td>
                <td>Customer Since</td>
                <td>Orders Placed</td>
            </tr>
            <?php
                $row = $custresult->fetch_assoc();
                
                # Find all orders for this customer.
                $selectorders = "SELECT ordernum, quantity, description, size, color, price, orderdate, estdelivery FROM ORDERS WHERE username = '".$row['username']."' ORDER BY ordernum";
                
                if (!($orders = $db->query($selectorders))) {  # This code will only run once
                    echo "Something went wrong. ".$db->errno.": ".$db->error;
                    exit();
                }
                
                $db->close();
            ?>
            <tr>
                <td><?php echo $row['fname'];?></td>   
                <td><?php echo $row['lname'];?></td>
                <td><?php echo $row['username'];?></td>
                <td><?php echo $row['email'];?></td>
                <td><?php 
                        $tmp = strtotime($row['custsince']);
                        $custsince = date("d M, Y", $tmp);
                        echo $custsince;
                    ?>
                </td>
                <td><?php echo $row['ordersplaced'];?></td>
            </tr>
        </table>
        <table>
            <h3>Orders for <?php echo $row['username'];?></h3>
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
                while ($currorder = $orders->fetch_assoc()) {
                    if (isset($currentordernum) && $currentordernum !== $currorder['ordernum']) {
                        echo "<td><strong>Order Total: </strong>$".number_format($total, 2)."</td><tr><td>------------------------</tr>";
                    }
                    if (!isset($currentordernum) || $currentordernum !== $currorder['ordernum']) {
                        echo "<td><strong>Order #:</strong> ".$currorder['ordernum']."</td>";
                        $total = 0;
                        $currentordernum = $currorder['ordernum'];
                    }
                    
                    $total += ($currorder['quantity'] * $currorder['price']);
            ?>
                <tr>
                    <td><?php echo $currorder['quantity'];?></td>
                    <td><?php echo $currorder['description'];?></td>
                    <td>
                        <?php
                            switch ($currorder['size']) {
                                case 1:
                                    echo "S";
                                    break;
                                case 2:
                                    echo "M";
                                    break;
                                case 3:
                                    echo "L";
                                    break;
                                case 4:
                                    echo "XL";
                                    break;
                                default:
                                    echo $currorder['size'];
                                    break;
                            }
                        ?>
                    </td>
                    <td><?php echo $currorder['color'];?></td>
                    <td><?php echo "$".number_format($currorder['price'], 2);?></td>
                    <td>
                        <?php
                            $tmp = strtotime($currorder['orderdate']);
                            $orderdate = date("d M, Y", $tmp);  # Variable only used for output, $estimated_delivery is stored in the database.
                            echo $orderdate;
                        ?>
                    </td>
                    <td>
                        <?php
                            $tmp = strtotime($currorder['estdelivery']);
                            $estdelivery = date("d M, Y", $tmp);
                            echo $estdelivery;
                        ?>
                    </td>
                </tr>
            <?php
                }
                echo "<td><strong>Order Total: </strong>$".number_format($total, 2)."</td>";  # Print the order total for the last order. The last order total is never reached in the while loop.
            ?>
        </table>
        <?php
            }
        ?>
    </body>
</html>