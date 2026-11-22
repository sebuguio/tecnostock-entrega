<?php
require_once __DIR__ . '/app/repositorio.php';
require_once __DIR__ . '/app/sesion.php';
exigirSesion();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if ($nombre === '') {
        $error = 'El nombre de la categoría es obligatorio.';
    } else {
        try {
            crearCategoria($nombre, $descripcion);
            definirAviso('success', "Categoría '{$nombre}' agregada correctamente.");
            header('Location: categorias.php');
            exit;
        } catch (PDOException $ex) {
            $error = ($ex->getCode() === '23000')
                ? "La categoría '{$nombre}' ya existe."
                : 'Error al crear categoría: ' . $ex->getMessage();
        }
    }
}

$categorias = listarCategorias(false);
$tituloPagina = 'Categorías - TecnoStock';
require_once __DIR__ . '/vistas/plantilla_arriba.php';
?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="caja-blanca">
            <div class="caja-blanca-header">
                <h5 class="fw-bold mb-0"><i class="bi bi-tag-fill text-warning me-2"></i>Nueva Categoría</h5>
            </div>
            <div class="p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger small fw-bold"><?= e($error) ?></div>
                <?php endif; ?>
                <form method="POST" data-validar="true" novalidate>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nombre *</label>
                        <input type="text" name="nombre" data-etiqueta="Nombre" class="form-control" placeholder="Ej: Audio y Micrófonos" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="2"></textarea>
                    </div>
                    <button type="submit" class="btn btn-tienda w-100">Agregar Categoría</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="caja-blanca">
            <div class="caja-blanca-header">
                <h5 class="fw-bold mb-0">Categorías Registradas</h5>
                <span class="badge bg-dark"><?= count($categorias) ?></span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categorias as $cat): ?>
                            <tr>
                                <td class="text-muted small">#<?= (int)$cat['id_categoria'] ?></td>
                                <td class="fw-bold"><?= e($cat['nombre']) ?></td>
                                <td class="small text-muted"><?= e($cat['descripcion']) ?></td>
                                <td>
                                    <span class="badge <?= $cat['estado'] ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= $cat['estado'] ? 'Activa' : 'Inactiva' ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/vistas/plantilla_abajo.php'; ?>
