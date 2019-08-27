<?php
    session_start();
    
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
   
    $login = parse_ini_file('../userdata/config.ini', true)['userlookup'];
    $db = new mysqli($login['server'], $login['username'], $login['password'], $login['database']);
    if ($db->connect_errno) {
        exit('<h1>Moorhead Bicycle</h1>Something went wrong. Please try again later.');
    }
    
    $query = "SELECT e.username, s.pwd FROM EMPLOYEE AS e, SITEUSER AS s WHERE e.username = ? AND s.username = e.username";
    if ($stmt = $db->prepare($query)) {
        if (!($stmt->bind_param("s", $username))) {
            exit('Something went wrong. '.$stmt->errno.': '.$stmt->error);
        }
        
        if (!($stmt->execute())) {
            exit('Something went wrong. '.$stmt->errno.': '.$stmt->error);
        }
        
        $results = $stmt->get_result();
        if ($results->num_rows !== 1) {
            echo "<h1>Moorhead Bicycle</h1>We could not find an account with that username. Please see your department supervisor or <a href=\"existing_user.html\">log in as a customer</a>.";
            exit();
        }
        
        $results = $results->fetch_assoc();
        
        if (password_verify($password, $results['pwd'])) {
            $_SESSION['username'] = htmlspecialchars($username);
            $_SESSION['sessiontype'] = "employee";
            header("Location: employeeportal.php");
        } else {
            echo "<h1>Moorhead Bicycle</h1>Your password is incorrect. <a href=\"employeelogin.html\">Go back</a>.";
        }
        
        $stmt->close();
    }
    $db->close();
?>