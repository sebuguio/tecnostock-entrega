<?php
require_once __DIR__ . '/app/repositorio.php';
require_once __DIR__ . '/app/sesion.php';
exigirSesion();

$idEditar = isset($_GET['id']) ? (int)$_GET['id'] : null;
$esEdicion = ($idEditar !== null && $idEditar > 0);
$producto = $esEdicion ? obtenerProducto($idEditar) : null;

if ($esEdicion && !$producto) {
    definirAviso('danger', 'El producto solicitado no existe.');
    header('Location: catalogo.php');
    exit;
}

$categorias = listarCategorias(true);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'codigo'       => strtoupper(trim($_POST['codigo'] ?? '')),
        'nombre'       => trim($_POST['nombre'] ?? ''),
        'id_categoria' => (int)($_POST['id_categoria'] ?? 0),
        'descripcion'  => trim($_POST['descripcion'] ?? ''),
        'precio'       => (float)($_POST['precio'] ?? -1),
        'stock_actual' => (int)($_POST['stock_actual'] ?? -1),
        'stock_minimo' => (int)($_POST['stock_minimo'] ?? -1),
    ];

    if ((!$esEdicion && $datos['codigo'] === '') || $datos['nombre'] === '' || $datos['id_categoria'] <= 0) {
        $error = 'Completa todos los campos obligatorios.';
    } elseif ($datos['precio'] < 0 || $datos['stock_actual'] < 0 || $datos['stock_minimo'] < 0) {
        $error = 'Regla de negocio: el precio, el stock actual y el stock mínimo no pueden ser negativos.';
    } else {
        try {
            guardarProducto($datos, $esEdicion ? $idEditar : null);
            definirAviso('success', $esEdicion ? 'Producto actualizado correctamente.' : 'Producto registrado en el catálogo.');
            header('Location: catalogo.php');
            exit;
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') {
                $error = "Regla de negocio: el código '{$datos['codigo']}' ya existe en el sistema.";
            } else {
                $error = 'Error al guardar: ' . $ex->getMessage();
            }
        }
    }
}

$tituloPagina = $esEdicion ? 'Editar Producto - TecnoStock' : 'Nuevo Producto - TecnoStock';
require_once __DIR__ . '/vistas/plantilla_arriba.php';
?>

<div class="caja-blanca mx-auto" style="max-width: 720px;">
    <div class="caja-blanca-header">
        <h5 class="fw-bold mb-0">
            <i class="bi <?= $esEdicion ? 'bi-pencil-square' : 'bi-plus-circle-fill' ?> text-warning me-2"></i>
            <?= $esEdicion ? 'Editar Producto: ' . e($producto['codigo']) : 'Registrar Nuevo Producto' ?>
        </h5>
        <a href="catalogo.php" class="btn btn-sm btn-outline-secondary fw-bold">Volver</a>
    </div>
    <div class="p-4">
        <?php if ($error): ?>
            <div class="alert alert-danger small fw-bold"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="POST" data-validar="true" novalidate>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold small">Código Único *</label>
                    <input type="text" name="codigo" data-etiqueta="Código" class="form-control"
                           value="<?= e($producto['codigo'] ?? ($_POST['codigo'] ?? '')) ?>"
                           placeholder="Ej: TEC-004" <?= $esEdicion ? 'readonly' : 'required' ?>>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-bold small">Nombre del Producto *</label>
                    <input type="text" name="nombre" data-etiqueta="Nombre" class="form-control"
                           value="<?= e($producto['nombre'] ?? ($_POST['nombre'] ?? '')) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Categoría *</label>
                    <select name="id_categoria" data-etiqueta="Categoría" class="form-select" required>
                        <option value="">Selecciona una categoría...</option>
                        <?php
                        $catSel = (int)($producto['id_categoria'] ?? ($_POST['id_categoria'] ?? 0));
                        foreach ($categorias as $c):
                        ?>
                            <option value="<?= $c['id_categoria'] ?>" <?= $catSel === (int)$c['id_categoria'] ? 'selected' : '' ?>>
                                <?= e($c['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Precio ($) *</label>
                    <input type="number" name="precio" data-etiqueta="Precio" class="form-control" min="0" step="1"
                           value="<?= e((string)($producto['precio'] ?? ($_POST['precio'] ?? ''))) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Stock Actual *</label>
                    <input type="number" name="stock_actual" data-etiqueta="Stock actual" class="form-control" min="0"
                           value="<?= e((string)($producto['stock_actual'] ?? ($_POST['stock_actual'] ?? '0'))) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Stock Mínimo (Alerta) *</label>
                    <input type="number" name="stock_minimo" data-etiqueta="Stock mínimo" class="form-control" min="0"
                           value="<?= e((string)($producto['stock_minimo'] ?? ($_POST['stock_minimo'] ?? '5'))) ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold small">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="2"><?= e($producto['descripcion'] ?? ($_POST['descripcion'] ?? '')) ?></textarea>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-end gap-2">
                <a href="catalogo.php" class="btn btn-light fw-bold">Cancelar</a>
                <button type="submit" class="btn btn-tienda">
                    <?= $esEdicion ? 'Actualizar Producto' : 'Guardar Producto' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/vistas/plantilla_abajo.php'; ?>
