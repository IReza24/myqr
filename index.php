<?php
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: src/auth/login.php');
    exit;
}

if ($_SESSION['role'] === 'admin') {
    header('Location: src/admin/index.php');
    exit;
} else {
    header('Location: src/user/index.php');
    exit;
}
?>