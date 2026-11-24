# TecnoStock · Sistema Web de Control de Inventario (MVP)

## 1. Análisis del Problema y Alcance
TecnoStock comercializa accesorios tecnológicos y llevaba su catálogo en hojas de cálculo manuales, originando duplicidad de códigos, descuadres de existencias y falta de alertas oportunas de reposición.

Este MVP web centraliza el catálogo, las categorías y el kardex de entradas/salidas sobre una base de datos relacional MySQL ejecutada en servidor local (XAMPP), sin frameworks complejos, aplicando PHP 8 con PDO, HTML5, Bootstrap 5 y validaciones en cliente (JavaScript) y servidor (PHP).

### Actor Principal
- **Encargado de inventario:** Administra el catálogo de productos, categorías y el registro auditable de entradas y salidas de existencias.

## 2. Trazabilidad de Requerimientos y Reglas de Negocio

| ID | Requerimiento / Regla | Implementación Verificable |
| :--- | :--- | :--- |
| **RF-01** | Inicio y cierre de sesión con usuarios activos | `acceso.php`, `salir.php`, `app/sesion.php` |
| **RF-02** | Registro y edición de productos (código único, sin negativos) | `ficha_producto.php`, `app/repositorio.php` (`guardarProducto`) |
| **RF-03** | Consulta del catálogo con búsqueda por código, nombre o categoría | `catalogo.php`, `app/repositorio.php` (`buscarProductos`) |
| **RF-04** | Desactivación lógica (sin borrado físico para conservar historial) | `estado_producto.php`, `app/repositorio.php` (`alternarEstadoProducto`) |
| **RF-05** | Administración de categorías | `categorias.php` |
| **RF-06** | Entradas y salidas con actualización automática y bloqueo de stock < 0 | `kardex.php`, `historial.php`, `app/repositorio.php` (`aplicarMovimientoStock`, `listarTodosLosMovimientos`) |
| **RF-07** | Alerta visual cuando `stock_actual < stock_minimo` | `catalogo.php` (banner rojo-naranja + filtro rápido "Solo bajo stock") |
| **RF-08** | Doble validación cliente (JavaScript) y servidor (PHP) | `publico/js/validaciones.js` y control en PHP en cada vista |

## 3. Arquitectura del Proyecto
- `app/`: Lógica compartida (`conexion.php`, `sesion.php`, `repositorio.php`).
- `vistas/`: Plantilla reutilizable (`plantilla_arriba.php`, `plantilla_abajo.php`).
- `publico/`: Recursos estáticos (`css/tienda.css` estilo eShop y `js/validaciones.js`).
- `bd/esquema_tecnostock.sql`: Script SQL reproducible con claves primarias, foráneas y restricciones `CHECK`/`UNIQUE`.
- Vistas principales en raíz: `catalogo.php`, `ficha_producto.php`, `kardex.php`, `historial.php`, `categorias.php`, `acceso.php`.

## 4. Puesta en Marcha Local (XAMPP)
1. Copiar la carpeta `tecnostock` en `C:\xampp\htdocs\`.
2. Iniciar **Apache** y **MySQL** en el panel de XAMPP.
3. Importar `bd/esquema_tecnostock.sql` desde phpMyAdmin (`http://localhost/phpmyadmin`).
4. Abrir `http://localhost/tecnostock/` en el navegador.
   - Al entrar por primera vez, el sistema inicializa automáticamente la cuenta **`admin@tecnostock.cl`** con contraseña **`tecno123`** usando `password_hash()` nativo.
   - Si ya existía una tabla `usuarios` con claves antiguas, abrir una sola vez `http://localhost/tecnostock/reparar_acceso.php`.

## 5. Plan de Pruebas Verificables

| Caso | Tipo | Acción ejecutada | Resultado esperado | Estado |
| :--- | :--- | :--- | :--- | :--- |
| **CP-01** | Normal | Iniciar sesión con `admin@tecnostock.cl` / `tecno123` | Acceso al catálogo con mensaje flash de bienvenida. | Aprobado |
| **CP-02** | Error (RN-01) | Registrar producto con código ya existente (`PER-101`) | Se captura excepción SQL `23000` y se muestra alerta clara. | Aprobado |
| **CP-03** | Límite (RN-02) | Ingresar precio `-100` o cantidad `0` | `validaciones.js` bloquea envío y marca campo en rojo; PHP también lo rechaza. | Aprobado |
| **CP-04** | Límite (RN-04) | Registrar salida de `10` unidades en `ALM-201` (Stock: `2`) | Transacción hace `rollBack()` y muestra error de stock insuficiente. | Aprobado |
| **CP-05** | Normal (RF-07) | Salida válida que deja `stock_actual < stock_minimo` | Stock se actualiza automáticamente y la tarjeta cambia a alerta roja `¡Bajo Stock!`. | Aprobado |
| **CP-06** | Integridad (RN-05) | Desactivar producto con movimientos asociados | Cambia `estado = 0` conservando sus registros en `historial.php`. | Aprobado |
