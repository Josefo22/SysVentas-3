<?php
require_once "../../db.php";

// Obtener parámetros de filtro
$fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-t');
$proveedor_id = $_GET['proveedor_id'] ?? '';

// Preparar la consulta base
$query = "SELECT c.*, p.nombre as proveedor
          FROM compras c
          JOIN proveedores p ON c.proveedor_id = p.id
          WHERE c.fecha BETWEEN :fecha_inicio AND :fecha_fin";
$params = [':fecha_inicio' => $fecha_inicio, ':fecha_fin' => $fecha_fin];

if ($proveedor_id) {
    $query .= " AND c.proveedor_id = :proveedor_id";
    $params[':proveedor_id'] = $proveedor_id;
}

$query .= " ORDER BY c.fecha DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener proveedores para el filtro
$stmtProveedores = $conn->query("SELECT id, nombre FROM proveedores ORDER BY nombre");
$proveedores = $stmtProveedores->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes de Compras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Mantener los mismos estilos del archivo original -->
</head>
<body>
    <?php include '../navbar.php'; ?>

    <div class="page-header">
        <div class="container">
            <br>
            <h2 class="text-center page-title">
                
                <i class="fas fa-file-alt me-2"></i>Reportes de Compras
            </h2>
            <form action="compras.php" method="get">
            <button type="submit" class="btn btn-primary mb-3">Volver</button>
        </form>
        </div>
    </div>

    <div class="container mb-5">
        <!-- Filtros -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros</h3>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" class="form-control" name="fecha_inicio" 
                               value="<?= $fecha_inicio ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha Fin</label>
                        <input type="date" class="form-control" name="fecha_fin" 
                               value="<?= $fecha_fin ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Proveedor</label>
                        <select class="form-select" name="proveedor_id">
                            <option value="">Todos</option>
                            <?php foreach ($proveedores as $proveedor): ?>
                                <option value="<?= $proveedor['id'] ?>" 
                                    <?= $proveedor_id == $proveedor['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($proveedor['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Filtrar
                        </button>
                        <a href="reportes_compras.php" class="btn btn-secondary">
                            <i class="fas fa-undo me-2"></i>Reiniciar
                        </a>
                        <button type="button" class="btn btn-success" onclick="exportarExcel()">
                            <i class="fas fa-file-excel me-2"></i>Exportar a Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Resultados -->
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-list me-2"></i>Resultados</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="tablaReporte">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Proveedor</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($compras as $compra): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($compra['fecha'])) ?></td>
                                    <td><?= htmlspecialchars($compra['proveedor']) ?></td>
                                    <td>$<?= number_format($compra['total'], 2) ?></td>
                                    <td>
                                        <span class="badge bg-success">Completada</span>
                                    </td>
                                    <td>
                                        <a href="compras_detalle.php?id=<?= $compra['id'] ?>" 
                                           class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>
    <script src="../../assets/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
    
    <script>
    function exportarExcel() {
        const tabla = document.getElementById('tablaReporte');
        const wb = XLSX.utils.table_to_book(tabla, {sheet: "Reporte de Compras"});
        XLSX.writeFile(wb, `Reporte_Compras_${new Date().toISOString().slice(0,10)}.xlsx`);
    }
    </script>
</body