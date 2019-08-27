<?php
    session_start();
    
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
   
    $login = parse_ini_file('../userdata/config.ini', true)['userlookup'];
    $db = new mysqli($login['server'], $login['username'], $login['password'], $login['database']);
    if ($db->connect_errno) {
        exit('<h1>Moorhead Bicycle</h1>Something went wrong. Please try again later.');
    }
    
    $query = "SELECT c.username, s.pwd FROM CUSTOMER AS c, SITEUSER AS s WHERE c.username = ? AND s.username = c.username";
    if ($stmt = $db->prepare($query)) {
        $stmt->bind_param("s", $username);
        
        if (!($stmt->execute())) {
            exit('Something went wrong. '.$stmt->errno.': '.$stmt->error);
        }
        
        $results = $stmt->get_result();
        
        if ($results->num_rows != 1) {  # Only one user should be found.
            echo "<h1>Moorhead Bicycle</h1>We could not find an account with that username. Please register <a href=\"login.html\">here</a> or <a href=\"existing_user.html\">go back</a>.";
            exit();
        }
        
        $stmt->close();
        
        $results = $results->fetch_assoc();  # Because only one result should be returned, fetch this result as an associative array
        
        if (password_verify($password, $results['pwd'])) {
            $_SESSION['username'] = htmlspecialchars($username);
            $_SESSION['sessiontype'] = "customer";
            header("Location: index.php");
        } else {
            echo "<h1>Moorhead Bicycle</h1>Your password is incorrect. <a href=\"existing_user.html\">Go back</a>.";
        }
    }
    $db->close();
?>