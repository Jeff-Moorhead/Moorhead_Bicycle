<?php
    session_start();
    require_once("../scripts/employee_login_check.php");
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Moorhead Bicycle - Admin Portal</title>
    </head>
    <body>
        <h1>Moorhead Bicycle - Admin Portal</h1>
        <a href="employeeportal.php">Employee Portal</a>&emsp;
        <a href="empsearchhandler.php">Employee Search</a><br><br>
        <?php
            $login = parse_ini_file('../userdata/config.ini', true)['supervisor'];
            $db = new mysqli($login['server'], $login['username'], $login['password'], $login['database']);
            if ($db->connect_errno) {
                echo "<h1>Moorhead Bicycle</h1>";
                exit('Something went wrong. Please try again later.');
            }
            
            $checkpermissions = "SELECT name FROM DEPARTMENT WHERE supervisor = ?";  # Make sure the current user is a department supervisor.
            if (!($checkstmt = $db->prepare($checkpermissions))) {
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
            
            $checkstmt->bind_result($department);
            $checkstmt->fetch();
            
            if (empty($department)) {
                echo "You do not have the proper permissions to access this page.";
                exit();
            }
            
            $checkstmt->close();
            
            if (isset($_POST) && !empty($_POST)) {
                $fname = trim($_POST['fname']);
                $lname = trim($_POST['lname']);
                $email = trim($_POST['email']);
                $dept = trim($_POST['dept']);
                $salary = trim($_POST['salary']);
                $username = trim($_POST['username']);
                $password = trim($_POST['password']);
                $confirm = trim($_POST['confirm']);
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                if (!is_numeric($salary)) {
                    echo "Salary must be a number.";
                    exit();
                }
                
                # Check if a username or email exists that is equal to the one the user entered
                $query = "SELECT e.username FROM EMPLOYEE e, SITEUSER s WHERE e.username = ? OR s.email = ?";
            
                if (!($stmt = $db->prepare($query))) {
                    echo "Something went wrong. ".$db->errno.": ".$db->error;
                    exit();
                }
                
                if (!($stmt->bind_param("ss", $username, $email))) {
                    echo "Something went wrong. ".$stmt->errno.": ".$stmt->error;
                    exit();
                }
                
                if (!($stmt->execute())) {
                    echo "Something went wrong. ".$stmt->errno.": ".$stmt->error;
                    exit();
                }
                
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    echo htmlspecialchars($username)." has already been registered. Please sign in <a href=\"employeelogin.html\">here</a> or <a href=\"adminhandler.php\">go back</a>.";
                    exit();
                }
                
                $stmt->close();
                
                if ($password !== $confirm) {
                    echo "Please confirm that the passwords match.";
                } else {
                
                    $insert_siteuser_query = "INSERT INTO SITEUSER VALUES (?, ?, ?, ?, ?)";
                    $insert_emp_query = "INSERT INTO EMPLOYEE VALUES(?, ?, ?)";
        
                    $date = date('Y-m-d');
        
                    if (!($stmtemp = $db->prepare($insert_emp_query))) {
                        exit('Something went wrong. '.$db->errno.': '.$db->error);
                    }
        
                    if (!($stmtemp->bind_param("ssi", $username, $dept, $salary))) {
                        exit('Something went wrong. '.$stmtemp->errno.': '.$stmtemp->error);
                    }
        
                    if ($stmtemp->execute()) {
                        $stmtemp->close();
                    } else {
                        exit('Something went wrong. '.$stmtemp->errno.': '.$stmtemp->error);
                    }
        
                    if (!($stmtsu = $db->prepare($insert_siteuser_query))) {
                        exit('Something went wrong. '.$db->errno.': '.$db->error);
                    }
        
                    if (!($stmtsu->bind_param("sssss", $username, $hashed_password, $email, $fname, $lname))) {
                        exit('Somehting went wrong. '.$stmtsu->errno.': '.$stmtsu->error);
                    }
        
                    if ($stmtsu->execute()) {
                        echo htmlspecialchars($username)." has been registered.";
                    } else {
                        exit('Something went wrong. '.$stmtsu->errno.': '.$stmtsu->error);
                    }
                    $stmtsu->close();
                }
                $db->close();
            }
        ?>
        <h2>New Employee Registration</h2>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="post">
            <table>
                <tr><td>First Name:</td><td><input type="text" name="fname" required></td></tr>
                <tr><td>Last Name:</td><td><input type="text" name="lname" required></td></tr>
                <tr><td>Email:</td><td><input type="email" name="email" required></td></tr>
                <tr><td>Department:</td><td>
                    <select name="dept">
                        <option value="Sales">Sales</option>
                        <option value="Repairs">Repairs</option>
                        <option value="Training">Training</option>
                        <option value="Purchasing">Purchasing</option>
                    </select>
                    </td></tr>
                <tr><td>Salary:</td><td><input type="number" name="salary"></td></tr>
                <tr><td>Username:</td><td><input type="text" name="username" required></td></tr>
                <tr><td>Password:</td><td><input type="password" name="password" required></td></tr>
                <tr><td>Confirm Password:</td><td><input type="password" name="confirm" required></td></tr>
                <tr><td><input type="submit" value="Register Employee"></td><td><input type="reset" value="Reset"></td></tr>
            </table>
        </form>
    </body>
</html>