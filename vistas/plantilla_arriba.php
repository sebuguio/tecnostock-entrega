<?php
require_once __DIR__ . '/../app/sesion.php';
exigirSesion();

$paginaActual = basename($_SERVER['PHP_SELF']);
$operador = operadorActual();
$aviso = leerAviso();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($tituloPagina ?? 'TecnoStock - Inventario') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@500;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="publico/css/tienda.css" rel="stylesheet">
</head>
<body>

<div class="barra-movil">
    <a href="catalogo.php" class="text-white text-decoration-none fw-bold fs-5">
        <i class="bi bi-bag-fill me-1"></i> TecnoStock
    </a>
    <button class="btn btn-light btn-sm fw-bold" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuMovil">
        <i class="bi bi-list fs-5"></i>
    </button>
</div>

<div class="offcanvas offcanvas-start text-white" style="background-color: #F37021;" tabindex="-1" id="menuMovil">
    <div class="offcanvas-header border-bottom border-white border-opacity-25">
        <h5 class="offcanvas-title fw-bold">TecnoStock</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column gap-2">
        <a class="enlace-menu <?= $paginaActual === 'catalogo.php' ? 'activo' : '' ?>" href="catalogo.php"><i class="bi bi-grid-fill"></i> Catálogo</a>
        <a class="enlace-menu <?= $paginaActual === 'ficha_producto.php' ? 'activo' : '' ?>" href="ficha_producto.php"><i class="bi bi-plus-square-fill"></i> Nuevo Producto</a>
        <a class="enlace-menu <?= $paginaActual === 'kardex.php' ? 'activo' : '' ?>" href="kardex.php"><i class="bi bi-arrow-left-right"></i> Entradas y Salidas</a>
        <a class="enlace-menu <?= $paginaActual === 'historial.php' ? 'activo' : '' ?>" href="historial.php"><i class="bi bi-clock-history"></i> Historial Completo</a>
        <a class="enlace-menu <?= $paginaActual === 'categorias.php' ? 'activo' : '' ?>" href="categorias.php"><i class="bi bi-tags-fill"></i> Categorías</a>
        <a class="enlace-menu <?= $paginaActual === 'usuarios.php' ? 'activo' : '' ?>" href="usuarios.php"><i class="bi bi-people-fill"></i> Usuarios</a>
        <hr class="border-white opacity-25 mt-auto">
        <div class="small mb-2"><i class="bi bi-person-circle me-1"></i> <?= e($operador['nombre']) ?></div>
        <a href="salir.php" class="btn btn-light btn-sm fw-bold text-danger">Cerrar Sesión</a>
    </div>
</div>

<div class="marco-app">
    <aside class="menu-lateral">
        <a href="catalogo.php" class="marca-lateral">
            <span class="marca-icono"><i class="bi bi-bag-fill"></i></span>
            <span>TecnoStock</span>
        </a>

        <nav class="lista-enlaces">
            <a class="enlace-menu <?= $paginaActual === 'catalogo.php' ? 'activo' : '' ?>" href="catalogo.php">
                <i class="bi bi-grid-fill fs-5"></i> Catálogo
            </a>
            <a class="enlace-menu <?= $paginaActual === 'ficha_producto.php' ? 'activo' : '' ?>" href="ficha_producto.php">
                <i class="bi bi-plus-square-fill fs-5"></i> Nuevo Producto
            </a>
            <a class="enlace-menu <?= $paginaActual === 'kardex.php' ? 'activo' : '' ?>" href="kardex.php">
                <i class="bi bi-arrow-left-right fs-5"></i> Entradas y Salidas
            </a>
            <a class="enlace-menu <?= $paginaActual === 'historial.php' ? 'activo' : '' ?>" href="historial.php">
                <i class="bi bi-clock-history fs-5"></i> Historial Completo
            </a>
            <a class="enlace-menu <?= $paginaActual === 'categorias.php' ? 'activo' : '' ?>" href="categorias.php">
                <i class="bi bi-tags-fill fs-5"></i> Categorías
            </a>
            <a class="enlace-menu <?= $paginaActual === 'usuarios.php' ? 'activo' : '' ?>" href="usuarios.php">
                <i class="bi bi-people-fill fs-5"></i> Usuarios
            </a>
        </nav>

        <div class="pie-lateral">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-person-circle fs-4"></i>
                <div class="lh-sm text-truncate">
                    <div class="fw-bold small text-truncate"><?= e($operador['nombre']) ?></div>
                    <div style="font-size: 0.72rem; opacity: 0.85;">Encargado de Inventario</div>
                </div>
            </div>
            <a href="salir.php" class="btn btn-light btn-sm w-100 fw-bold text-dark">
                <i class="bi bi-box-arrow-left me-1"></i> Cerrar Sesión
            </a>
        </div>
    </aside>

    <main class="zona-contenido">
        <?php if ($aviso): ?>
            <div class="alert alert-<?= e($aviso['tipo']) ?> alert-dismissible fade show shadow-sm fw-bold small" role="alert">
                <?= e($aviso['texto']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
