<?php
    session_start();

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $password = trim($_POST['password']);
    $confirm_pwd = trim($_POST['confirm']);
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $login = parse_ini_file('../userdata/config.ini', true)['registration'];
    $db = new mysqli($login['server'], $login['username'], $login['password'], $login['database']);
    if ($db->connect_errno) {
        echo "<h1>Moorhead Bicycle</h1>";
        exit('Something went wrong. Please try again later.');
    }
    
    # Check if a username or email exists that is equal to the one the user entered
    $query = "SELECT c.username FROM CUSTOMER AS c, SITEUSER AS s WHERE c.username = ? OR s.email = ?";
            
    if ($stmt = $db->prepare($query)) {
        if (!($stmt->bind_param("ss", $username, $email))) {
            exit('Something went wrong. '.$stmt->errno.': '.$stmt->error);
        }
        
        if (!($stmt->execute())) {
            exit('Somehthing went wrong. '.$stmt->errno.': '.$stmt->error);
        }
        
        $result = $stmt->get_result();
        $stmt->close();
    }
    
    if ($result->num_rows > 0) {
        echo "<h1>Moorhead Bicycle</h1>You have already registered. Please login <a href=\"existing_user.html\">here</a>.";
        exit(0);
    }
    
    if ($password !== $confirm_pwd) {
        echo "<h1>Moorhead Bicycle</h1>";
        exit("Please confirm that both passwords are the same.<br>
        <a href=\"login.html\">Go back</a>");
    }
    
    $insert_siteuser_query = "INSERT INTO SITEUSER VALUES (?, ?, ?, ?, ?)";
    $insert_cust_query = "INSERT INTO CUSTOMER VALUES(?, ?)";
    
    $date = date('Y-m-d');
    
    if (!($stmtcust = $db->prepare($insert_cust_query))) {
        exit('Something went wrong. '.$db->errno.': '.$db->error);
    }
    
    if (!($stmtcust->bind_param("ss", $username, $date))) {
        exit('Something went wrong. '.$stmtcust->errno.': '.$stmtcust->error);
    }
    
    if ($stmtcust->execute()) {
        $stmtcust->close();
    } else {
        exit('Something went wrong. '.$stmtcust->errno.': '.$stmtcust->error);
    }
    
    if (!($stmtsu = $db->prepare($insert_siteuser_query))) {
        exit('Something went wrong. '.$db->errno.': '.$db->error);
    }
    
    if (!($stmtsu->bind_param("sssss", $username, $hashed_password, $email, $fname, $lname))) {
        exit('Somehting went wrong. '.$stmtsu->errno.': '.$stmtsu->error);
    }
    
    if ($stmtsu->execute()) {
        $_SESSION['username'] = htmlspecialchars($username);
        $_SESSION['sessiontype'] = "customer";
        header("Location: index.php");
    } else {
        exit('Something went wrong. '.$stmtsu->errno.': '.$stmtsu->error);
    }
    $stmtsu->close();

    $db->close();
?>
    