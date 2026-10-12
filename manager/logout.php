<?php
/**
 * GrihGO Digital & Tech — Manager Logout
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

unset($_SESSION['manager_id'], $_SESSION['manager_email'], $_SESSION['manager_name'], $_SESSION['manager_role'], $_SESSION['manager_last_activity']);

header('Location: login.php?msg=logout');
exit;
