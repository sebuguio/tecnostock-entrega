<?php
/**
 * Conexión centralizada a MySQL mediante PDO.
 * Configurado con consultas preparadas nativas y modo de excepciones.
 */
function obtenerConexion(): PDO {
    static $conexion = null;

    if ($conexion === null) {
        $servidor = 'localhost';
        $baseDatos = 'tecnostock';
        $usuario = 'root';
        $clave = '';

        $dsn = "mysql:host={$servidor};dbname={$baseDatos};charset=utf8mb4";
        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $conexion = new PDO($dsn, $usuario, $clave, $opciones);
        } catch (PDOException $ex) {
            http_response_code(500);
            die("No fue posible conectar con MySQL. Verifica que XAMPP esté activo y que la base 'tecnostock' exista. Detalle: " . htmlspecialchars($ex->getMessage()));
        }
    }

    return $conexion;
}
