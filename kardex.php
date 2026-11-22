<?php
require_once __DIR__ . '/app/repositorio.php';
require_once __DIR__ . '/app/sesion.php';
exigirSesion();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idProducto  = (int)($_POST['id_producto'] ?? 0);
    $tipo        = $_POST['tipo'] ?? '';
    $cantidad    = (int)($_POST['cantidad'] ?? 0);
    $observacion = trim($_POST['observacion'] ?? '');
    $operador    = operadorActual();

    try {
        $nuevoStock = aplicarMovimientoStock($idProducto, $tipo, $cantidad, $observacion, (int)$operador['id']);
        definirAviso('success', "Movimiento ({$tipo}) registrado. Stock actualizado automáticamente a {$nuevoStock} unidades.");
        header('Location: kardex.php');
        exit;
    } catch (Throwable $ex) {
        $error = $ex->getMessage();
    }
}

$productosActivos = array_filter(buscarProductos(), fn($p) => (int)$p['estado'] === 1);
$historial = listarMovimientosRecientes(50);

$tituloPagina = 'Entradas y Salidas - TecnoStock';
require_once __DIR__ . '/vistas/plantilla_arriba.php';
?>

<div class="mb-4">
    <h2 class="fw-bold mb-1">Control de Movimientos (Kardex)</h2>
    <div class="text-muted small">Cada entrada o salida actualiza el stock automáticamente dentro de una transacción PDO.</div>
</div>

<div class="row g-4">
    <!-- Formulario de registro -->
    <div class="col-lg-5">
        <div class="caja-blanca">
            <div class="caja-blanca-header">
                <h5 class="fw-bold mb-0"><i class="bi bi-arrow-left-right text-warning me-2"></i>Registrar Movimiento</h5>
            </div>
            <div class="p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger small fw-bold"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" data-validar="true" novalidate>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Producto Activo *</label>
                        <select name="id_producto" data-etiqueta="Producto" class="form-select" required>
                            <option value="">Selecciona un producto...</option>
                            <?php foreach ($productosActivos as $p): ?>
                                <option value="<?= (int)$p['id_producto'] ?>">
                                    [<?= e($p['codigo']) ?>] <?= e($p['nombre']) ?> (Stock: <?= (int)$p['stock_actual'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Tipo *</label>
                            <select name="tipo" data-etiqueta="Tipo" class="form-select" required>
                                <option value="ENTRADA">ENTRADA (+)</option>
                                <option value="SALIDA">SALIDA (-)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">Cantidad *</label>
                            <input type="number" name="cantidad" data-etiqueta="Cantidad" class="form-control" min="1" value="1" required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold small">Observación / Respaldo</label>
                        <input type="text" name="observacion" class="form-control" placeholder="Ej: Compra factura #45 / Venta mostrador">
                    </div>
                    <button type="submit" class="btn btn-tienda w-100">Confirmar Movimiento</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Historial auditable -->
    <div class="col-lg-7">
        <div class="caja-blanca">
            <div class="caja-blanca-header">
                <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2"></i>Historial Reciente</h5>
                <span class="badge bg-dark"><?= count($historial) ?> registros</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Producto</th>
                            <th>Tipo</th>
                            <th>Cant.</th>
                            <th>Responsable</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($historial)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted small">Sin movimientos registrados aún.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($historial as $m): ?>
                            <tr>
                                <td class="small text-muted"><?= e($m['fecha_hora']) ?></td>
                                <td>
                                    <div class="fw-bold small"><?= e($m['codigo']) ?> - <?= e($m['nombre_producto']) ?></div>
                                    <?php if ($m['observacion']): ?>
                                        <div class="text-muted" style="font-size: 0.75rem;"><?= e($m['observacion']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge <?= $m['tipo'] === 'ENTRADA' ? 'bg-primary' : 'bg-danger' ?>">
                                        <?= e($m['tipo']) ?>
                                    </span>
                                </td>
                                <td class="fw-bold"><?= (int)$m['cantidad'] ?></td>
                                <td class="small"><?= e($m['nombre_usuario']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/vistas/plantilla_abajo.php'; ?>
