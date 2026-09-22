<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';

$_SESSION = [];
session_destroy();

redirect(url('/auth/login.php'));