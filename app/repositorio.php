<?php
/**
 * Capa de acceso a datos (consultas preparadas PDO) separada de las vistas.
 * Agrupa operaciones de usuarios, categorías, productos y movimientos.
 */
require_once __DIR__ . '/conexion.php';

/* ===================== USUARIOS ===================== */

function asegurarUsuarioInicial(): void {
    $db = obtenerConexion();
    $total = (int)$db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    if ($total === 0) {
        $hash = password_hash('tecno123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO usuarios (nombre, correo, clave, estado) VALUES (?, ?, ?, 1)");
        $stmt->execute(['Encargado TecnoStock', 'admin@tecnostock.cl', $hash]);
    }
}

function buscarUsuarioPorCorreo(string $correo): ?array {
    $db = obtenerConexion();
    $stmt = $db->prepare("SELECT id_usuario, nombre, correo, clave, estado FROM usuarios WHERE correo = ? LIMIT 1");
    $stmt->execute([$correo]);
    $fila = $stmt->fetch();
    return $fila ?: null;
}

/* ===================== CATEGORÍAS ===================== */

function listarCategorias(bool $soloActivas = false): array {
    $db = obtenerConexion();
    $sql = "SELECT id_categoria, nombre, descripcion, estado FROM categorias";
    if ($soloActivas) {
        $sql .= " WHERE estado = 1";
    }
    $sql .= " ORDER BY nombre ASC";
    return $db->query($sql)->fetchAll();
}

function crearCategoria(string $nombre, string $descripcion): void {
    $db = obtenerConexion();
    $stmt = $db->prepare("INSERT INTO categorias (nombre, descripcion, estado) VALUES (?, ?, 1)");
    $stmt->execute([$nombre, $descripcion]);
}

/* ===================== PRODUCTOS ===================== */

function buscarProductos(string $termino = '', int $idCategoria = 0, bool $soloBajoStock = false): array {
    $db = obtenerConexion();
    $condiciones = [];
    $parametros = [];

    if ($termino !== '') {
        $condiciones[] = "(p.codigo LIKE :t1 OR p.nombre LIKE :t2 OR c.nombre LIKE :t3)";
        $like = "%{$termino}%";
        $parametros['t1'] = $like;
        $parametros['t2'] = $like;
        $parametros['t3'] = $like;
    }

    if ($idCategoria > 0) {
        $condiciones[] = "p.id_categoria = :cat";
        $parametros['cat'] = $idCategoria;
    }

    if ($soloBajoStock) {
        $condiciones[] = "(p.estado = 1 AND p.stock_actual < p.stock_minimo)";
    }

    $where = $condiciones ? ('WHERE ' . implode(' AND ', $condiciones)) : '';

    $sql = "SELECT p.id_producto, p.codigo, p.nombre, p.descripcion, p.precio,
                   p.stock_actual, p.stock_minimo, p.estado, p.id_categoria,
                   c.nombre AS nombre_categoria
            FROM productos p
            INNER JOIN categorias c ON c.id_categoria = p.id_categoria
            {$where}
            ORDER BY p.estado DESC, (p.stock_actual < p.stock_minimo) DESC, p.nombre ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute($parametros);
    return $stmt->fetchAll();
}

function obtenerProducto(int $idProducto): ?array {
    $db = obtenerConexion();
    $stmt = $db->prepare("SELECT * FROM productos WHERE id_producto = ? LIMIT 1");
    $stmt->execute([$idProducto]);
    $fila = $stmt->fetch();
    return $fila ?: null;
}

function guardarProducto(array $datos, ?int $idExistente = null): void {
    $db = obtenerConexion();

    if ($idExistente === null) {
        $sql = "INSERT INTO productos (codigo, nombre, descripcion, precio, stock_actual, stock_minimo, estado, id_categoria)
                VALUES (:codigo, :nombre, :descripcion, :precio, :stock_actual, :stock_minimo, 1, :id_categoria)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'codigo'       => $datos['codigo'],
            'nombre'       => $datos['nombre'],
            'descripcion'  => $datos['descripcion'],
            'precio'       => $datos['precio'],
            'stock_actual' => $datos['stock_actual'],
            'stock_minimo' => $datos['stock_minimo'],
            'id_categoria' => $datos['id_categoria'],
        ]);
    } else {
        $sql = "UPDATE productos
                SET nombre = :nombre,
                    descripcion = :descripcion,
                    precio = :precio,
                    stock_actual = :stock_actual,
                    stock_minimo = :stock_minimo,
                    id_categoria = :id_categoria
                WHERE id_producto = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            'nombre'       => $datos['nombre'],
            'descripcion'  => $datos['descripcion'],
            'precio'       => $datos['precio'],
            'stock_actual' => $datos['stock_actual'],
            'stock_minimo' => $datos['stock_minimo'],
            'id_categoria' => $datos['id_categoria'],
            'id'           => $idExistente,
        ]);
    }
}

function alternarEstadoProducto(int $idProducto): bool {
    $db = obtenerConexion();
    $stmt = $db->prepare("UPDATE productos SET estado = CASE WHEN estado = 1 THEN 0 ELSE 1 END WHERE id_producto = ?");
    return $stmt->execute([$idProducto]);
}

/* ===================== MOVIMIENTOS (KARDEX) ===================== */

function aplicarMovimientoStock(int $idProducto, string $tipo, int $cantidad, string $observacion, int $idUsuario): int {
    if ($cantidad <= 0) {
        throw new InvalidArgumentException("La cantidad del movimiento debe ser mayor a cero.");
    }
    if (!in_array($tipo, ['ENTRADA', 'SALIDA'], true)) {
        throw new InvalidArgumentException("El tipo de movimiento debe ser ENTRADA o SALIDA.");
    }

    $db = obtenerConexion();
    $db->beginTransaction();

    try {
        // Bloqueo de fila para integridad de stock
        $stmtProd = $db->prepare("SELECT stock_actual, estado, nombre FROM productos WHERE id_producto = ? FOR UPDATE");
        $stmtProd->execute([$idProducto]);
        $prod = $stmtProd->fetch();

        if (!$prod) {
            throw new RuntimeException("El producto indicado no existe.");
        }
        if ((int)$prod['estado'] !== 1) {
            throw new RuntimeException("No se pueden registrar movimientos sobre un producto desactivado.");
        }

        $stockActual = (int)$prod['stock_actual'];
        if ($tipo === 'SALIDA' && $cantidad > $stockActual) {
            throw new RuntimeException("Stock insuficiente para '{$prod['nombre']}'. Disponible actual: {$stockActual} u.");
        }

        $nuevoStock = ($tipo === 'ENTRADA') ? ($stockActual + $cantidad) : ($stockActual - $cantidad);

        $stmtMov = $db->prepare(
            "INSERT INTO movimientos (tipo, cantidad, observacion, id_producto, id_usuario)
             VALUES (:tipo, :cantidad, :observacion, :id_producto, :id_usuario)"
        );
        $stmtMov->execute([
            'tipo'        => $tipo,
            'cantidad'    => $cantidad,
            'observacion' => $observacion,
            'id_producto' => $idProducto,
            'id_usuario'  => $idUsuario,
        ]);

        $stmtUpd = $db->prepare("UPDATE productos SET stock_actual = :stock WHERE id_producto = :id");
        $stmtUpd->execute([
            'stock' => $nuevoStock,
            'id'    => $idProducto,
        ]);

        $db->commit();
        return $nuevoStock;
    } catch (Throwable $err) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $err;
    }
}

function listarMovimientosRecientes(int $limite = 50): array {
    $db = obtenerConexion();
    $sql = "SELECT m.id_movimiento, m.fecha_hora, m.tipo, m.cantidad, m.observacion,
                   p.codigo, p.nombre AS nombre_producto, u.nombre AS nombre_usuario
            FROM movimientos m
            INNER JOIN productos p ON p.id_producto = m.id_producto
            INNER JOIN usuarios u ON u.id_usuario = m.id_usuario
            ORDER BY m.fecha_hora DESC, m.id_movimiento DESC
            LIMIT :lim";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':lim', $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function listarTodosLosMovimientos(string $termino = '', string $tipo = ''): array {
    $db = obtenerConexion();
    $condiciones = [];
    $parametros = [];

    if ($termino !== '') {
        $condiciones[] = "(p.codigo LIKE :t1 OR p.nombre LIKE :t2 OR m.observacion LIKE :t3 OR u.nombre LIKE :t4)";
        $like = "%{$termino}%";
        $parametros['t1'] = $like;
        $parametros['t2'] = $like;
        $parametros['t3'] = $like;
        $parametros['t4'] = $like;
    }

    if (in_array($tipo, ['ENTRADA', 'SALIDA'], true)) {
        $condiciones[] = "m.tipo = :tipo";
        $parametros['tipo'] = $tipo;
    }

    $where = $condiciones ? ('WHERE ' . implode(' AND ', $condiciones)) : '';

    $sql = "SELECT m.id_movimiento, m.fecha_hora, m.tipo, m.cantidad, m.observacion,
                   p.codigo, p.nombre AS nombre_producto, u.nombre AS nombre_usuario
            FROM movimientos m
            INNER JOIN productos p ON p.id_producto = m.id_producto
            INNER JOIN usuarios u ON u.id_usuario = m.id_usuario
            {$where}
            ORDER BY m.fecha_hora DESC, m.id_movimiento DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($parametros);
    return $stmt->fetchAll();
}
