<?php
    // Only redirect to login.html (registration page) if no user is logged in to this session or if the session type is 'employee'
    // and another page other than employeeportal.php is calling.
    if (!isset($_SESSION['username']) || $_SESSION['sessiontype'] !== 'employee') {
        header("Location: login.html");
        exit();
    }
?>