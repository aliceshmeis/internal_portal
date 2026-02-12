<?php
session_start();
session_destroy();
header('Location: /internal_portal/app/views/auth/login.php');
exit;
?>