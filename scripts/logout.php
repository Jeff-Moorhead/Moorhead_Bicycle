<?php
    session_start();
    $_SESSION = array();
    session_destroy();
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Moorhead Bicycle - Goodbye!</title>
    </head>
    <body>
        <h1>Moorhead Bicycle - See you later!</h1>
        <?php
            echo "You have been successfully logged out. Come back soon!<br><br>";
        ?>
        Return to <a href="login.html">Registration</a> or <a href="existing_user.html">Login</a>
        <?php
            exit();
        ?>
    </body>
</html