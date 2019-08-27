<?php
    // Only redirect to login.html (registration page) if no user is logged in to this session or if the session type is 'employee'
    // and another page other than employeeportal.php is calling.
    if (!isset($_SESSION['username']) || $_SESSION['sessiontype'] !== 'customer') {
        header("Location: login.html");
        exit();
    }
    
    # || ($_SESSION['sessiontype'] === 'employee' && basename($_SERVER["SCRIPT_FILENAME"], '.php') !== 'employeeportal'))
    # The above was intended to prevent users from illegally accessing the employee portal. It only works for employeeportal.php,
    # which means no other employee script can use it. A revamp is needed here.
?>