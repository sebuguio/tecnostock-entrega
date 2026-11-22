<?php
require_once __DIR__ . '/app/repositorio.php';
$tituloPagina = 'Catálogo - TecnoStock';
require_once __DIR__ . '/vistas/plantilla_arriba.php';

$busqueda     = trim($_GET['q'] ?? '');
$catFiltro    = (int)($_GET['cat'] ?? 0);
$soloAlertas  = isset($_GET['bajo']) && $_GET['bajo'] === '1';

$categorias = listarCategorias(true);
$productos  = buscarProductos($busqueda, $catFiltro, $soloAlertas);

$cantAlertas = 0;
foreach ($productos as $prod) {
    if ((int)$prod['estado'] === 1 && (int)$prod['stock_actual'] < (int)$prod['stock_minimo']) {
        $cantAlertas++;
    }
}
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 class="fw-bold mb-1">Catálogo de Productos</h2>
        <div class="text-muted small">
            <strong><?= count($productos) ?></strong> artículos encontrados
            <?php if ($cantAlertas > 0): ?>
                · <span class="text-danger fw-bold"><i class="bi bi-exclamation-circle-fill"></i> <?= $cantAlertas ?> con alerta de stock bajo</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="ficha_producto.php" class="btn btn-tienda"><i class="bi bi-plus-lg me-1"></i> Nuevo Producto</a>
        <a href="kardex.php" class="btn btn-dark fw-bold rounded-3"><i class="bi bi-arrow-left-right me-1"></i> Entradas / Salidas</a>
    </div>
</div>

<!-- Barra de búsqueda y filtros -->
<form method="GET" class="caja-blanca p-3 mb-4">
    <div class="row g-2 align-items-center">
        <div class="col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control border-start-0 ps-0" placeholder="Buscar por código, nombre o categoría..." value="<?= e($busqueda) ?>">
            </div>
        </div>
        <div class="col-md-3">
            <select name="cat" class="form-select">
                <option value="0">Todas las categorías</option>
                <?php foreach ($categorias as $c): ?>
                    <option value="<?= $c['id_categoria'] ?>" <?= $catFiltro === (int)$c['id_categoria'] ? 'selected' : '' ?>>
                        <?= e($c['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="bajo" value="1" id="chkBajo" <?= $soloAlertas ? 'checked' : '' ?>>
                <label class="form-check-label small fw-bold text-danger" for="chkBajo">Solo bajo stock</label>
            </div>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-tienda w-100">Filtrar</button>
            <?php if ($busqueda !== '' || $catFiltro > 0 || $soloAlertas): ?>
                <a href="catalogo.php" class="btn btn-outline-secondary" title="Limpiar"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
        </div>
    </div>
</form>

<?php if (empty($productos)): ?>
    <div class="caja-blanca p-5 text-center text-muted">
        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
        <h5 class="fw-bold">Sin resultados</h5>
        <p class="small mb-0">No hay productos que coincidan con el filtro aplicado.</p>
    </div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($productos as $p): ?>
            <?php
            $activo = (int)$p['estado'] === 1;
            $esBajoStock = $activo && ((int)$p['stock_actual'] < (int)$p['stock_minimo']);
            ?>
            <div class="col-12 col-md-6 col-xl-4">
                <article class="tarjeta-item <?= !$activo ? 'desactivada' : '' ?>">
                    <div class="cabecera-item <?= $esBajoStock ? 'alerta-stock' : '' ?>">
                        <div>
                            <span class="etiqueta-codigo"><?= e($p['codigo']) ?></span>
                            <div class="small mt-2 fw-bold opacity-75">
                                <i class="bi bi-tag-fill me-1"></i><?= e($p['nombre_categoria']) ?>
                            </div>
                        </div>
                        <div>
                            <?php if (!$activo): ?>
                                <span class="badge bg-secondary">Desactivado</span>
                            <?php elseif ($esBajoStock): ?>
                                <span class="badge bg-white text-danger fw-bold shadow-sm">
                                    <i class="bi bi-exclamation-triangle-fill"></i> ¡Bajo Stock!
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success">Activo</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="p-3 d-flex flex-column flex-grow-1">
                        <h5 class="fw-bold mb-1 text-truncate" title="<?= e($p['nombre']) ?>"><?= e($p['nombre']) ?></h5>
                        <p class="text-muted small mb-3 flex-grow-1">
                            <?= $p['descripcion'] ? e($p['descripcion']) : 'Sin descripción registrada.' ?>
                        </p>

                        <div class="d-flex justify-content-between align-items-end border-top pt-3 mb-3">
                            <div>
                                <div class="text-muted fw-bold" style="font-size: 0.7rem; text-transform: uppercase;">Precio unitario</div>
                                <div class="precio-grande">$<?= number_format((float)$p['precio'], 0, ',', '.') ?></div>
                            </div>
                            <div class="text-end">
                                <div class="small">
                                    Existencias:
                                    <span class="fw-bold fs-6 <?= $esBajoStock ? 'text-danger' : 'text-dark' ?>">
                                        <?= (int)$p['stock_actual'] ?>
                                    </span>
                                </div>
                                <div class="text-muted" style="font-size: 0.75rem;">Mínimo: <?= (int)$p['stock_minimo'] ?> u.</div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="ficha_producto.php?id=<?= (int)$p['id_producto'] ?>" class="btn btn-sm btn-outline-dark fw-bold flex-grow-1">
                                <i class="bi bi-pencil-square me-1"></i> Editar
                            </a>
                            <form method="POST" action="estado_producto.php" class="d-inline">
                                <input type="hidden" name="id_producto" value="<?= (int)$p['id_producto'] ?>">
                                <button type="submit" class="btn btn-sm <?= $activo ? 'btn-outline-secondary' : 'btn-outline-success' ?> fw-bold">
                                    <i class="bi <?= $activo ? 'bi-slash-circle' : 'bi-check-circle' ?> me-1"></i>
                                    <?= $activo ? 'Desactivar' : 'Activar' ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/vistas/plantilla_abajo.php'; ?>
