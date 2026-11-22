<?php
/**
 * Utilidades de sesión, protección de rutas, mensajes flash y escape seguro.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function estaAutenticado(): bool {
    return isset($_SESSION['operador']['id']);
}

function exigirSesion(): void {
    if (!estaAutenticado()) {
        definirAviso('warning', 'Debes iniciar sesión para acceder al inventario.');
        header('Location: acceso.php');
        exit;
    }
}

function operadorActual(): array {
    return $_SESSION['operador'] ?? ['id' => 0, 'nombre' => 'Invitado', 'correo' => ''];
}

function definirAviso(string $tipo, string $texto): void {
    $_SESSION['aviso_flash'] = [
        'tipo' => $tipo,
        'texto' => $texto
    ];
}

function leerAviso(): ?array {
    if (!isset($_SESSION['aviso_flash'])) {
        return null;
    }
    $aviso = $_SESSION['aviso_flash'];
    unset($_SESSION['aviso_flash']);
    return $aviso;
}

function e(?string $valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}
