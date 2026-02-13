<?php
session_start();

require_once __DIR__ . '/../../../core/AuthHelper.php';

AuthHelper::logout();

header('Location: login.php');
exit;
?>