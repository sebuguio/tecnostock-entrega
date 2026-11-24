<?php
require_once __DIR__ . '/app/sesion.php';
require_once __DIR__ . '/app/repositorio.php';

if (estaAutenticado()) {
    header('Location: catalogo.php');
    exit;
}

// Si la tabla usuarios está vacía, crea automáticamente el usuario inicial con hash válido del servidor PHP
asegurarUsuarioInicial();

$error = '';
$aviso = leerAviso();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $clave  = $_POST['clave'] ?? '';

    if ($correo === '' || $clave === '') {
        $error = 'Ingresa tu correo y contraseña.';
    } else {
        $usuario = buscarUsuarioPorCorreo($correo);
        if ($usuario && (int)$usuario['estado'] === 1 && password_verify($clave, $usuario['clave'])) {
            $_SESSION['operador'] = [
                'id'     => (int)$usuario['id_usuario'],
                'nombre' => $usuario['nombre'],
                'correo' => $usuario['correo'],
            ];
            definirAviso('success', 'Bienvenido/a, ' . $usuario['nombre'] . '.');
            header('Location: catalogo.php');
            exit;
        }
        $error = 'Credenciales inválidas o cuenta desactivada.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - TecnoStock</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@500;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #F37021 0%, #FF8E3C 100%);
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 16px;
        }
        .panel-acceso {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.2);
        }
        .encabezado-acceso {
            background: #242629;
            color: #fff;
            text-align: center;
            padding: 28px 20px;
        }
        .icono-bolsa {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: #F37021;
            display: inline-grid;
            place-items: center;
            font-size: 1.7rem;
            margin-bottom: 10px;
        }
        .btn-entrar {
            background: #F37021;
            color: #fff;
            font-weight: 800;
            border: 3px solid transparent;
            border-radius: 10px;
            padding: 10px;
        }
        .btn-entrar:hover {
            background: #D65A10;
            color: #fff;
            border-color: #00C3E3;
        }
    </style>
</head>
<body>
<div class="panel-acceso">
    <div class="encabezado-acceso">
        <div class="icono-bolsa"><i class="bi bi-bag-fill"></i></div>
        <h4 class="fw-bold mb-0">TecnoStock</h4>
        <div class="small text-white-50">Control de Inventario y Existencias</div>
    </div>
    <div class="p-4">
        <?php if ($aviso): ?>
            <div class="alert alert-<?= e($aviso['tipo']) ?> py-2 small fw-bold"><?= e($aviso['texto']) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small fw-bold">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= e($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" data-validar="true" novalidate>
            <div class="mb-3">
                <label class="form-label fw-bold small text-secondary">Correo electrónico</label>
                <input type="email" name="correo" data-etiqueta="Correo" class="form-control" required placeholder="Ingresa tu correo">
            </div>
            <div class="mb-4">
                <label class="form-label fw-bold small text-secondary">Contraseña</label>
                <input type="password" name="clave" data-etiqueta="Contraseña" class="form-control" required placeholder="Ingresa tu contraseña">
            </div>
            <button type="submit" class="btn btn-entrar w-100">
                Ingresar al Sistema <i class="bi bi-arrow-right ms-1"></i>
            </button>
        </form>
    </div>
</div>
<script src="publico/js/validaciones.js"></script>
</body>
</html>
