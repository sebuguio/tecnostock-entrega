<?php
require_once __DIR__ . '/app/sesion.php';
session_unset();
session_destroy();
header('Location: acceso.php');
exit;
