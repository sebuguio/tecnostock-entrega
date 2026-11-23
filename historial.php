<?php
require_once __DIR__ . '/app/repositorio.php';
$tituloPagina = 'Historial Completo - TecnoStock';
require_once __DIR__ . '/vistas/plantilla_arriba.php';

$busqueda   = trim($_GET['q'] ?? '');
$tipoFiltro = $_GET['tipo'] ?? '';

$movimientos = listarTodosLosMovimientos($busqueda, $tipoFiltro);
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h2 class="fw-bold mb-1">Historial Completo de Movimientos</h2>
        <div class="text-muted small">
            Mostrando <strong><?= count($movimientos) ?></strong> registros en total
        </div>
    </div>
    <a href="kardex.php" class="btn btn-tienda">
        <i class="bi bi-plus-lg me-1"></i> Registrar Movimiento
    </a>
</div>

<form method="GET" class="caja-blanca p-3 mb-4">
    <div class="row g-2 align-items-center">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control border-start-0 ps-0"
                       placeholder="Buscar por código, producto, observación o responsable..."
                       value="<?= e($busqueda) ?>">
            </div>
        </div>
        <div class="col-md-3">
            <select name="tipo" class="form-select">
                <option value="">Todos los tipos</option>
                <option value="ENTRADA" <?= $tipoFiltro === 'ENTRADA' ? 'selected' : '' ?>>Solo ENTRADAS</option>
                <option value="SALIDA" <?= $tipoFiltro === 'SALIDA' ? 'selected' : '' ?>>Solo SALIDAS</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-1">
            <button type="submit" class="btn btn-tienda w-100">Filtrar</button>
            <?php if ($busqueda !== '' || $tipoFiltro !== ''): ?>
                <a href="historial.php" class="btn btn-outline-secondary" title="Limpiar"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
        </div>
    </div>
</form>

<div class="caja-blanca">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Fecha y Hora</th>
                    <th>Producto</th>
                    <th>Tipo</th>
                    <th>Cantidad</th>
                    <th>Responsable</th>
                    <th>Observación</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($movimientos)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">No se encontraron movimientos registrados.</td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($movimientos as $m): ?>
                    <tr>
                        <td class="text-muted small">#<?= (int)$m['id_movimiento'] ?></td>
                        <td class="small text-muted"><?= e($m['fecha_hora']) ?></td>
                        <td>
                            <strong><?= e($m['codigo']) ?></strong> - <?= e($m['nombre_producto']) ?>
                        </td>
                        <td>
                            <span class="badge <?= $m['tipo'] === 'ENTRADA' ? 'bg-primary' : 'bg-danger' ?>">
                                <?= e($m['tipo']) ?>
                            </span>
                        </td>
                        <td class="fw-bold"><?= (int)$m['cantidad'] ?></td>
                        <td class="small"><?= e($m['nombre_usuario']) ?></td>
                        <td class="small text-muted"><?= $m['observacion'] ? e($m['observacion']) : '—' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/vistas/plantilla_abajo.php'; ?>
