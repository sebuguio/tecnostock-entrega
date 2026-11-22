<?php
require_once __DIR__ . '/app/sesion.php';

if (estaAutenticado()) {
    header('Location: catalogo.php');
} else {
    header('Location: acceso.php');
}
exit;
