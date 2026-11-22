<?php
/**
 * Utilidad de apoyo para inicializar o restablecer el usuario administrador
 * usando el algoritmo password_hash() nativo de tu instalación local de PHP.
 */
require_once __DIR__ . '/app/conexion.php';

$db = obtenerConexion();
$hashSeguro = password_hash('tecno123', PASSWORD_DEFAULT);

$stmt = $db->prepare(
    "INSERT INTO usuarios (nombre, correo, clave, estado)
     VALUES ('Encargado TecnoStock', 'admin@tecnostock.cl', ?, 1)
     ON DUPLICATE KEY UPDATE clave = VALUES(clave), estado = 1"
);
$stmt->execute([$hashSeguro]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso Reparado - TecnoStock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="card shadow-sm p-4 text-center" style="max-width: 440px;">
        <h4 class="text-success fw-bold mb-3">Usuario Administrador Listo</h4>
        <p class="mb-2">Se actualizó la clave directamente con <code>password_hash()</code> de tu PHP local:</p>
        <div class="alert alert-secondary text-start small">
            <strong>Correo:</strong> admin@tecnostock.cl<br>
            <strong>Contraseña:</strong> tecno123
        </div>
        <a href="acceso.php" class="btn btn-primary fw-bold w-100">Ir a Iniciar Sesión</a>
    </div>
</body>
</html>
