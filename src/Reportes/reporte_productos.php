<?php
require_once "../../db.php";  // Incluimos el archivo de conexión con la base de datos

// Función para obtener las categorías
function getCategorias($conn) {
    return $conn->query("SELECT id, nombre FROM categorias ORDER BY nombre")->fetchAll();
}

// Función para obtener los productos con filtros
function getProductos($conn, $filtros = []) {
    $where = ["p.activo = 1"]; // Siempre mostrar solo productos activos
    $params = [];

    if (!empty($filtros['categoria_id'])) {
        $where[] = "p.categoria_id = ?";
        $params[] = $filtros['categoria_id'];
    }
    if (isset($filtros['stock_min']) && $filtros['stock_min'] !== '') {
        $where[] = "p.stock <= ?";
        $params[] = $filtros['stock_min'];
    }

    $sql = "SELECT p.*, c.nombre as categoria_nombre 
            FROM productos p 
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE " . implode(" AND ", $where) . "
            ORDER BY p.nombre";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Verificar que la conexión a la base de datos esté bien
if (!$conn) {
    die("Error al conectar a la base de datos.");
}

// Obtener datos
$categorias = getCategorias($conn);
$productos = getProductos($conn, [
    'categoria_id' => $_GET['categoria_id'] ?? '',
    'stock_min' => $_GET['stock_min'] ?? ''
]);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Productos Activos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Reporte de Productos Activos</h2>
            <span class="badge bg-info">Total: <?php echo count($productos); ?> productos</span>
        </div>
        
        <!-- Formulario de filtros -->
        <form method="GET" class="mb-4 card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Categoría:</label>
                            <select name="categoria_id" class="form-control">
                                <option value="">Todas las categorías</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?php echo $categoria['id']; ?>"
                                            <?php echo ($_GET['categoria_id'] ?? '') == $categoria['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($categoria['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Stock menor o igual a:</label>
                            <input type="number" name="stock_min" class="form-control" 
                                   value="<?php echo htmlspecialchars($_GET['stock_min'] ?? ''); ?>"
                                   min="0">
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="mb-3 w-100">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        <!-- Tabla de productos -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th class="text-end">Precio</th>
                        <th class="text-center">Stock</th>
                        <th>Categoría</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($productos)): ?>
                    <tr>
                        <td colspan="7" class="text-center">No se encontraron productos con los filtros seleccionados</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($productos as $producto): ?>
                        <tr>
                            <td><?php echo $producto['id']; ?></td>
                            <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($producto['descripcion'] ?? 'Sin descripción'); ?></td>
                            <td class="text-end">$<?php echo number_format($producto['precio'], 2); ?></td>
                            <td class="text-center">
                                <span class="badge <?php echo $producto['stock'] <= 10 ? 'bg-danger' : 'bg-success'; ?>">
                                    <?php echo $producto['stock']; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($producto['categoria_nombre'] ?? 'Sin categoría'); ?></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-info" onclick="mostrarMovimientos(<?php echo $producto['id']; ?>)">
                                    Ver Movimientos
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para movimientos -->
    <div class="modal fade" id="movimientosModal" tabindex="-1" aria-labelledby="movimientosModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="movimientosModalLabel">Historial de Ventas del Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="movimientosContent">
                <p class="text-center">Cargando historial...</p>
            </div>
        </div>
    </div>
</div>


    <?php include '../footer.php'; ?>
    <script src="../../assets/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function mostrarMovimientos(productoId) {
    const modal = new bootstrap.Modal(document.getElementById('movimientosModal'));
    const contentDiv = document.getElementById('movimientosContent');

    // Cargar movimientos mediante AJAX
    fetch(`get_movimientos.php?producto_id=${productoId}`)
        .then(response => response.text())
        .then(html => {
            contentDiv.innerHTML = html;
            modal.show();
        })
        .catch(error => {
            contentDiv.innerHTML = `<div class="alert alert-danger">Error al cargar los movimientos: ${error}</div>`;
            modal.show();
        });
}

    </script>
</body>
</html>
