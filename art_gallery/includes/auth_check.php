<?php
require_once 'session_helper.php';
if (!is_logged_in()) {
    header("Location: ../user/login.php");
    exit;
}
