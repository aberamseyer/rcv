<?php

$login = true;

require $_SERVER['DOCUMENT_ROOT']."/inc/init.php";

$_SESSION = [];
session_destroy();
setcookie(session_name(), '', time() - 3600, '/');
redirect("/login");
