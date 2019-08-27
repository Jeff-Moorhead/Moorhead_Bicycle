<?php
    session_start();

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $salary = (int) trim($_POST['salary']);
    $department = trim($_POST['department']);
    $password = trim($_POST['password']);
    $confirm_pwd = trim($_POST['confirm']);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $login = parse_ini_file('../userdata/config.ini', true)['registration'];
    $db = new mysqli($login['server'], $login['username'], $login['password'], $login['database']);
    if ($db->connect_errno) {
        echo "<h1>Moorhead Bicycle</h1>";
        exit('Something went wrong. Please try again later.');
    }
    
    $query = "SELECT username FROM EMPLOYEE WHERE username = ?";
            
    if ($stmt = $db->prepare($query)) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    }
    
    if ($result->num_rows > 0) {
        echo "<h1>Moorhead Bicycle</h1>You have already registered. Please login <a href=\"employeelogin.html\">here</a>.";
        exit(0);
    }
    
    if ($password !== $confirm_pwd) {
        echo "</h1>Moorhead Bicycle</h1>";
        exit("Please confirm that both passwords are the same.<br>
        <a href=\"login.html\">Go back</a>");
    }
    
    $insert_siteuser_query = "INSERT INTO SITEUSER VALUES (?, ?, ?, ?, ?)";
    $insert_cust_query = "INSERT INTO EMPLOYEE VALUES(?, ?, ?)";
    
    $stmtcust = $db->prepare($insert_cust_query);
    $stmtcust->bind_param("sss", $username, $department, $salary);
    
    if ($stmtcust->execute()) {
        $stmtcust->close();
    } else {
        echo "Woops! Something went wrong.";
        exit();
    }
    
    $stmtsu = $db->prepare($insert_siteuser_query);  # Statement Site User
    $stmtsu->bind_param("sssss", $username, $hashed_password, $email, $fname, $lname);
    if ($stmtsu->execute()) {
        $_SESSION['username'] = htmlspecialchars($username);
        $_SESSION['sessiontype'] = "employee";
        header("Location: employeeportal.php");
    } else {
        echo "Woops! Something went wrong.";
        exit();
    }
    $stmtsu->close();

    $db->close();
?>