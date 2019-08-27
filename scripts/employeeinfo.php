<?php
    session_start();
    require_once("../scripts/employee_login_check.php");
    
    $login = parse_ini_file('../userdata/config.ini', true)['userlookup'];
    $db = new mysqli($login['server'], $login['username'], $login['password'], $login['database']);
    
    if ($db->connect_errno) {
        echo "<h1>Moorhead Bicycle</h1>";
        exit('Something went wrong. Please try again later.');
    }
    
    $selectuser = "SELECT e.*, u.fname AS superfirst, u.lname AS superlast FROM EMPDATA e, SITEUSER u, DEPARTMENT d WHERE e.username = ? AND d.name = e.deptname AND d.supervisor = u.username";
    
    if (!($stmt = $db->prepare($selectuser))) {
        echo "Something went wrong. ".$db->errno.": ".$db->error;
        exit();
    }
    
    if (!($stmt->bind_param('s', $_SESSION['username']))) {
        echo "Something went wrong. ".$stmt->errno.": ".$stmt->error;
        exit();
    }
    
    if (!($stmt->execute())) {
        echo "Something went wrong. ".$stmt->errno.": ".$stmt->error;
        exit();
    }
    
    $results = $stmt->get_result();
    
    if ($results->num_rows !== 1) {
        echo "Something went wrong. Rows found: ".$results->num_rows;  # Only one username should be found as that is a key field.
        exit();
    }
    
    $stmt->close();
    $db->close();
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Moorhead Bicycle - Employee Info</title>
        <link rel="stylesheet" href="styles/tables.css">
    </head>
    <body>
        <h1>Moorhead Bicycle - Employee Info for <?php echo $_SESSION['username'];?></h1>
        <table>
            <tr class="prodheader">
                <td>Username</td>
                <td>Email</td>
                <td>First Name</td>
                <td>Last Name</td>
                <td>Department</td>
                <td>Supervisor</td>
                <td>Salary</td>
            </tr>
            <tr>
                <?php
                    while ($row = $results->fetch_assoc()) {
                ?>
                <td><?php echo $row['username'];?></td>
                <td><?php echo $row['email'];?></td>
                <td><?php echo $row['fname'];?></td>
                <td><?php echo $row['lname'];?></td>
                <td><?php echo $row['deptname'];?></td>
                <td><?php echo ($row['supervisor'] === $row['username'] ? "N/A" : $row['superfirst']." ".$row['superlast']);?></td>
                <td><?php echo "$".number_format($row['salary'], 2);?></td>
                <?php
                    }
                ?>
            </tr>
        </table>
        <a href="employeeportal.php">Return to Employee Portal</a>
    </body>
</html>