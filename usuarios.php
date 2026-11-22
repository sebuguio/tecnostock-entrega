<?php
require_once __DIR__ . '/app/repositorio.php';
require_once __DIR__ . '/app/sesion.php';
exigirSesion();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $clave  = $_POST['clave_nueva'] ?? '';

        if ($nombre === '' || $correo === '' || strlen($clave) < 6) {
            $error = 'Completa nombre, correo y una contraseña de al menos 6 caracteres.';
        } else {
            try {
                crearUsuario($nombre, $correo, $clave);
                definirAviso('success', "Usuario '{$nombre}' creado correctamente.");
                header('Location: usuarios.php');
                exit;
            } catch (PDOException $ex) {
                $error = ($ex->getCode() === '23000')
                    ? "El correo '{$correo}' ya se encuentra registrado."
                    : 'Error al registrar usuario: ' . $ex->getMessage();
            }
        }
    } elseif ($accion === 'reset_clave') {
        $idUser = (int)($_POST['id_usuario'] ?? 0);
        $nuevaClave = $_POST['clave_nueva'] ?? '';
        if ($idUser > 0 && strlen($nuevaClave) >= 6) {
            actualizarClaveUsuario($idUser, $nuevaClave);
            definirAviso('success', 'Contraseña restablecida correctamente con password_hash().');
            header('Location: usuarios.php');
            exit;
        } else {
            $error = 'La nueva contraseña debe tener al menos 6 caracteres.';
        }
    }
}

$usuarios = listarUsuarios();
$tituloPagina = 'Usuarios - TecnoStock';
require_once __DIR__ . '/vistas/plantilla_arriba.php';
?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="caja-blanca">
            <div class="caja-blanca-header">
                <h5 class="fw-bold mb-0"><i class="bi bi-person-plus-fill text-warning me-2"></i>Crear Usuario</h5>
            </div>
            <div class="p-4">
                <?php if ($error): ?>
                    <div class="alert alert-danger small fw-bold"><?= e($error) ?></div>
                <?php endif; ?>
                <form method="POST" data-validar="true" novalidate>
                    <input type="hidden" name="accion" value="crear">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nombre completo *</label>
                        <input type="text" name="nombre" data-etiqueta="Nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Correo electrónico *</label>
                        <input type="email" name="correo" data-etiqueta="Correo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Contraseña (mín. 6 caracteres) *</label>
                        <input type="password" name="clave_nueva" data-etiqueta="Contraseña" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-tienda w-100">Registrar Usuario</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="caja-blanca">
            <div class="caja-blanca-header">
                <h5 class="fw-bold mb-0">Cuentas Registradas</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Estado</th>
                            <th>Restablecer Clave</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td class="fw-bold small"><?= e($u['nombre']) ?></td>
                                <td class="small text-muted"><?= e($u['correo']) ?></td>
                                <td><span class="badge <?= $u['estado'] ? 'bg-success' : 'bg-secondary' ?>"><?= $u['estado'] ? 'Activo' : 'Inactivo' ?></span></td>
                                <td>
                                    <form method="POST" class="d-flex gap-1">
                                        <input type="hidden" name="accion" value="reset_clave">
                                        <input type="hidden" name="id_usuario" value="<?= (int)$u['id_usuario'] ?>">
                                        <input type="password" name="clave_nueva" class="form-control form-control-sm" placeholder="Nueva clave" required minlength="6" style="max-width: 130px;">
                                        <button type="submit" class="btn btn-sm btn-outline-dark fw-bold">Cambiar</button>
                                    </form>
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
