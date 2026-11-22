<?php
require_once __DIR__ . '/app/sesion.php';
require_once __DIR__ . '/app/repositorio.php';
exigirSesion();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id_producto'] ?? 0);
    if ($id > 0) {
        alternarEstadoProducto($id);
        definirAviso('info', 'Estado del producto actualizado (borrado lógico aplicado).');
    }
}

header('Location: catalogo.php');
exit;
