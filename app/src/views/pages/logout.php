<?php
// public_html/logout.php
session_start();
$_SESSION = array();
session_destroy();
header('Location: home');
exit;