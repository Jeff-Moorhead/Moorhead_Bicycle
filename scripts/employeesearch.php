<?php
    session_start();
    require_once("employee_login_check.php");
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Moorhead Bicycle - Employee Search</title>
        <link href="styles/tables.css" rel="stylesheet">
    </head>
    <body>
        <h1>Moorhead Bicycle - Employee Search</h1>
        <a href="adminhandler.php">Admin Portal</a>&emsp;
        <a href="employeeportal.php">Employee Portal</a><br><br>
        
        <?php
            $login = parse_ini_file('../userdata/config.ini', true)['supervisor'];
            $db = new mysqli($login['server'], $login['username'], $login['password'], $login['database']);
            if ($db->connect_errno) {
                echo "<h1>Moorhead Bicycle</h1>";
                exit('Something went wrong. Please try again later.');
            }
            
            $checkauth = "SELECT name FROM DEPARTMENT WHERE supervisor = ?";
            if (!($checkstmt = $db->prepare($checkauth))) {
                echo "Something went wrong. ".$db->errno.": ".$db->error;
                exit();
            }
            
            if (!($checkstmt->bind_param("s", $_SESSION['username']))) {
                echo "Something went wrong. ".$checkstmt->errno.": ".$checkstmt->error;
                exit();
            }
            
            if (!($checkstmt->execute())) {
                echo "Something went wrong. ".$checkstmt->errno.": ".$checkstmt->error;
                exit();
            }
            
            $checkstmt->store_result();
            if ($checkstmt->num_rows < 1) {
                echo "You do not have the proper permissions to access this page.<br>";
                exit();
            }
            
            if ($checkstmt->num_rows > 1) {
                echo "Something went wrong. Multiple users found.";
                exit();
            }
            
            $checkstmt->free_result();
            $checkstmt->close();
            
            if (isset($_GET['lname']) && isset($_GET['email'])) {
                if (empty($_GET['lname'] || empty($_GET['email']))) {
                    echo "Please fill out all employee information.";
                    exit();
                }
                
                $lname = trim($_GET['lname']);
                $email = trim($_GET['email']);
                
                $search = "SELECT e.*, u.fname AS superfirst, u.lname AS superlast FROM EMPDATA e, SITEUSER u, DEPARTMENT d WHERE e.lname = ? AND e.email = ? AND d.name = e.deptname AND d.supervisor = u.username";
                
                if (!($searchstmt = $db->prepare($search))) {
                    echo "Something went wrong. ".$db->errno.": ".$db->error;
                    exit();
                }
                
                if (!$searchstmt->bind_param("ss", $lname, $email)) {
                    echo "Something went wrong. ".$searchstmt->errno.": ".$searchstmt->error;
                    exit();
                }
                
                if (!$searchstmt->execute()) {
                    echo "Something went wrong. ".$searchstmt->errno.": ".$searchstmt->error;
                    exit();
                }
                
                $employees = $searchstmt->get_result();
                
                $searchstmt->close();
            }
            $db->close();
        ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="get">
            <table>
                <th>Employee Lookup</th>
                <tr>
                    <td>Last Name:</td><td><input type="text" name="lname" required></td>
                </tr>
                <tr>
                    <td>Email:</td><td><input type="email" name="email" required></td>
                </tr>
                <tr>
                    <td><input type="submit" value="Search Employees"></td>
                </tr>
            </table>
        </form>
        <?php
            if (isset($employees)) {
                if ($employees->num_rows === 0) {
                    echo "No employees found.";
                }
                
                while ($row = $employees->fetch_assoc()) {
                ?>
                
                <table>
                    <tr class="prodheader">
                        <td>First Name</td>
                        <td>Last Name</td>
                        <td>Username</td>
                        <td>Email</td>
                        <td>Department</td>
                        <td>Supervisor</td>
                        <td>Salary</td>
                    </tr>
                    <tr>
                        <td><?php echo $row['fname'];?></td>
                        <td><?php echo $row['lname'];?></td>
                        <td><?php echo $row['username'];?></td>
                        <td><?php echo $row['email'];?></td>
                        <td><?php echo $row['deptname'];?></td>
                        <td>
                            <?php
                                echo ($row['supervisor'] === $row['username'] ? "N/A" : $row['superfirst']." ".$row['superlast']);
                            ?>
                        </td>
                        <td><?php echo "$".number_format($row['salary'], 2);?></td>
                    </tr>
                </table>
        <?php
                }
            }
        ?>
    </body>
</html>