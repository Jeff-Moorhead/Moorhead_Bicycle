<?php
// this script handles all logins, add-to-carts, checkouts, and logouts.
if (!empty($_POST['i_action']))
{
    $action = str_replace('.', '', $_POST['i_action']);
    $action = str_replace('/', '', $action);
    if (file_exists("../scripts/$action".'.php'))
        require_once("../scripts/$action".'.php');
}
else {
    header("HTTP/1.0 404 Not Found", true, 404);
    exit();
}
?>