<?php
require_once "../../db.php";


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// reporte_compras.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../navbar.php'; ?>
    <div class="container mt-4">
        <h2>Reporte de Ventas</h2>
        
        <!-- Formulario de filtros -->
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <label>Fecha Inicio:</label>
                    <input type="date" name="fecha_inicio" class="form-control" value="<?php echo $_GET['fecha_inicio'] ?? ''; ?>">
                </div>
                <div class="col-md-3">
                    <label>Fecha Fin:</label>
                    <input type="date" name="fecha_fin" class="form-control" value="<?php echo $_GET['fecha_fin'] ?? ''; ?>">
                </div>
                <div class="col-md-3">
                    <label>Cliente:</label>
                    <select name="cliente_id" class="form-control">
                        <option value="">Todos los clientes</option>
                        <?php
                        $stmt = $pdo->query("SELECT id, nombre FROM clientes ORDER BY nombre");
                        while ($row = $stmt->fetch()) {
                            $selected = ($_GET['cliente_id'] ?? '') == $row['id'] ? 'selected' : '';
                            echo "<option value='{$row['id']}' $selected>{$row['nombre']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary d-block">Filtrar</button>
                </div>
            </div>
        </form>

        <!-- Tabla de resultados -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="bg-light">
                    <tr>
                        <th>ID Venta</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Usuario</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Construir la consulta con filtros
                    $where = [];
                    $params = [];

                    if (!empty($_GET['fecha_inicio'])) {
                        $where[] = "v.fecha >= ?";
                        $params[] = $_GET['fecha_inicio'] . ' 00:00:00';
                    }
                    if (!empty($_GET['fecha_fin'])) {
                        $where[] = "v.fecha <= ?";
                        $params[] = $_GET['fecha_fin'] . ' 23:59:59';
                    }
                    if (!empty($_GET['cliente_id'])) {
                        $where[] = "v.cliente_id = ?";
                        $params[] = $_GET['cliente_id'];
                    }

                    $sql = "SELECT v.*, 
                           c.nombre as cliente_nombre,
                           u.nombre as usuario_nombre
                           FROM ventas v
                           LEFT JOIN clientes c ON v.cliente_id = c.id
                           LEFT JOIN usuarios u ON v.usuario_id = u.id";

                    if (!empty($where)) {
                        $sql .= " WHERE " . implode(" AND ", $where);
                    }

                    $sql .= " ORDER BY v.fecha DESC";

                    // Ejecutar la consulta
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);

                    // Variables para totales
                    $total_ventas = 0;

                    while ($row = $stmt->fetch()) {
                        $total_ventas += $row['total'];
                        ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['fecha'])); ?></td>
                            <td><?php echo htmlspecialchars($row['cliente_nombre'] ?? 'Cliente General'); ?></td>
                            <td><?php echo htmlspecialchars($row['usuario_nombre']); ?></td>
                            <td class="text-end">$<?php echo number_format($row['total'], 2); ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" 
                                        onclick="verDetalles(<?php echo $row['id']; ?>)">
                                    Ver Detalles
                                </button>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="4" class="text-end">Total General:</td>
                        <td class="text-end">$<?php echo number_format($total_ventas, 2); ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Modal para detalles -->
    <div class="modal fade" id="detallesModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalles de Venta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detallesContent">
                    <!-- El contenido se cargará mediante AJAX -->
                </div>
            </div>
        </div>
    </div>
    <?php include '../footer.php'; ?>
    <script src="../../assets/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function verDetalles(ventaId) {
        const modal = new bootstrap.Modal(document.getElementById('detallesModal'));
        const contentDiv = document.getElementById('detallesContent');
        
        // Cargar detalles mediante AJAX
        fetch(`get_detalles_venta.php?id=${ventaId}`)
            .then(response => response.text())
            .then(html => {
                contentDiv.innerHTML = html;
                modal.show();
            })
            .catch(error => {
                console.error('Error:', error);
                contentDiv.innerHTML = 'Error al cargar los detalles';
            });
    }
    </script>
</body>
</html>