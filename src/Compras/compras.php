<?php
require_once "../../db.php";

// Obtener las últimas compras
$stmt = $conn->prepare("SELECT c.id, c.total, p.nombre AS proveedor, c.fecha 
                        FROM compras c 
                        JOIN proveedores p ON c.proveedor_id = p.id 
                        ORDER BY c.fecha DESC LIMIT 5");
$stmt->execute();
$compras = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Calcular compras de hoy
$stmtHoy = $conn->prepare("SELECT COALESCE(SUM(total), 0) as total 
                          FROM compras 
                          WHERE DATE(fecha) = CURDATE()");
$stmtHoy->execute();
$comprasHoy = $stmtHoy->fetch(PDO::FETCH_ASSOC)['total'];

// Calcular compras de la semana
$stmtSemana = $conn->prepare("SELECT COALESCE(SUM(total), 0) as total 
                             FROM compras 
                             WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)");
$stmtSemana->execute();
$comprasSemana = $stmtSemana->fetch(PDO::FETCH_ASSOC)['total'];

// Calcular compras del mes
$stmtMes = $conn->prepare("SELECT COALESCE(SUM(total), 0) as total 
                          FROM compras 
                          WHERE MONTH(fecha) = MONTH(CURRENT_DATE()) 
                          AND YEAR(fecha) = YEAR(CURRENT_DATE())");
$stmtMes->execute();
$comprasMes = $stmtMes->fetch(PDO::FETCH_ASSOC)['total'];

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Compras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        .page-header {
            background-color: #fff;
            padding: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .page-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .action-buttons {
            gap: 10px;
        }

        .btn {
            border-radius: 5px;
            padding: 8px 16px;
            transition: all 0.3s;
        }

        .btn-success {
            background-color: #2ecc71;
            border: none;
        }

        .btn-success:hover {
            background-color: #27ae60;
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background-color: #3498db;
            border: none;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }
        
        .btn-info {
            background-color: #00bcd4;
            border: none;
            color: white;
        }
        
        .btn-info:hover {
            background-color: #00a5bb;
            transform: translateY(-2px);
            color: white;
        }

        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .card-header {
            border-radius: 8px 8px 0 0 !important;
            background-color: #34495e;
            color: white;
            padding: 15px 20px;
        }

        .card-body {
            padding: 25px;
        }
        
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .table thead th {
            background-color: #34495e;
            color: white;
            padding: 12px;
        }

        .table tbody td {
            padding: 12px;
            vertical-align: middle;
        }
        
        .total-badge {
            padding: 6px 12px;
            border-radius: 15px;
            font-weight: 500;
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .date-badge {
            padding: 6px 12px;
            border-radius: 15px;
            font-weight: 500;
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .action-column {
            display: flex;
            gap: 5px;
        }
    </style>
</head>
<body>

<?php include '../navbar.php'; ?>

<div class="page-header">
    <div class="container">
        <h2 class="text-center page-title">
            <i class="fas fa-shopping-bag me-2"></i>Gestión de Compras
        </h2>
        <div class="d-flex justify-content-center action-buttons">
            <a href="compras_nueva.php" class="btn btn-success">
                <i class="fas fa-plus-circle me-2"></i>Registrar Nueva Compra
            </a>
            <a href="compras_listar.php" class="btn btn-primary">
                <i class="fas fa-list-alt me-2"></i>Ver Listado Completo
            </a>
        </div>
    </div>
</div>

<div class="container mb-5">
    <!-- Card para Resumen de Compras -->
    <div class="card">
        <div class="card-header">
            <h3 class="mb-0"><i class="fas fa-history me-2"></i>Últimas Compras Realizadas</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag me-2"></i>ID</th>
                            <th><i class="fas fa-building me-2"></i>Proveedor</th>
                            <th><i class="fas fa-calendar-alt me-2"></i>Fecha</th>
                            <th><i class="fas fa-hand-holding-usd me-2"></i>Total</th>
                            <th><i class="fas fa-cogs me-2"></i>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($compras) > 0): ?>
                            <?php foreach ($compras as $compra): ?>
                                <tr>
                                    <td><?= $compra['id'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-truck text-secondary me-2"></i>
                                            <?= htmlspecialchars($compra['proveedor']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="date-badge">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= date('d/m/Y', strtotime($compra['fecha'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="total-badge">
                                            <i class="fas fa-dollar-sign me-1"></i>
                                            <?= number_format($compra['total'], 2) ?>
                                        </span>
                                    </td>
                                    <td class="action-column">
                                        <a href="compras_detalle.php?id=<?= $compra['id'] ?>" 
                                           class="btn btn-info btn-sm">
                                            <i class="fas fa-eye me-1"></i>Ver Detalles
                                        </a>
                                        <a href="compras_imprimir.php?id=<?= $compra['id'] ?>" 
                                           class="btn btn-secondary btn-sm">
                                            <i class="fas fa-print me-1"></i>Imprimir
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="fas fa-inbox fa-2x mb-3 text-muted"></i>
                                    <p class="mb-0">No hay compras registradas aún</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Cards de Información Adicional -->
    <div class="row">
        <!-- Card de Estadísticas Rápidas -->
        <div class="col-md-6 mb-4">
    <div class="card h-100">
        <div class="card-header">
            <h4 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Resumen de Compras</h4>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-center">
                <div class="text-center px-4">
                    <i class="fas fa-calendar-day fa-2x mb-2 text-primary"></i>
                    <h5>Hoy</h5>
                    <h4 class="text-primary">$<?php echo number_format($comprasHoy, 2); ?></h4>
                </div>
                <div class="text-center px-4">
                    <i class="fas fa-calendar-week fa-2x mb-2 text-success"></i>
                    <h5>Esta semana</h5>
                    <h4 class="text-success">$<?php echo number_format($comprasSemana, 2); ?></h4>
                </div>
                <div class="text-center px-4">
                    <i class="fas fa-calendar-alt fa-2x mb-2 text-info"></i>
                    <h5>Este mes</h5>
                    <h4 class="text-info">$<?php echo number_format($comprasMes, 2); ?></h4>
                </div>
            </div>
        </div>
    </div>
</div>
        
        <!-- Card de Acciones Rápidas -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-bolt me-2"></i>Acciones Rápidas</h4>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="proveedores_listar.php" class="btn btn-outline-primary">
                            <i class="fas fa-address-book me-2"></i>Gestionar Proveedores
                        </a>
                        <a href="reportes_compras.php" class="btn btn-outline-success">
                            <i class="fas fa-file-excel me-2"></i>Generar Reportes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
<script src="../../assets/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>