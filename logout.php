<?php
require_once 'includes/config.php';
auth_logout();
header('Location: login.php');
exit;
