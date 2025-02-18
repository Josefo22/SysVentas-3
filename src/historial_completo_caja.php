<?php
session_start();
require_once "../db.php";

// Obtener fechas del formulario o establecer valores predeterminados
$fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
$fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;

// Pagination setup
$registrosPorPagina = 20;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina - 1) * $registrosPorPagina;

// Base query for records
$sqlBase = "SELECT c.* 
            FROM caja c 
            WHERE c.estado = 'cerrada'";

// Add date filters if provided
$params = [];
if ($fechaInicio && $fechaFin) {
    $sqlBase .= " AND DATE(c.fecha_apertura) BETWEEN :fecha_inicio AND :fecha_fin";
    $params[':fecha_inicio'] = $fechaInicio;
    $params[':fecha_fin'] = $fechaFin;
}

// Add pagination - usando bindValue para LIMIT
$sqlBase .= " LIMIT ?, ?";

// Execute main query
$stmt = $conn->prepare($sqlBase);

// Bind the LIMIT parameters separately
if ($fechaInicio && $fechaFin) {
    $stmt->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
    $stmt->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
}
// Bind the LIMIT parameters
$stmt->bindValue(1, $offset, PDO::PARAM_INT);
$stmt->bindValue(2, $registrosPorPagina, PDO::PARAM_INT);

$stmt->execute();
$cierres = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total records
$sqlCount = "SELECT COUNT(*) as total 
             FROM caja c 
             WHERE c.estado = 'cerrada'";

if ($fechaInicio && $fechaFin) {
    $sqlCount .= " AND DATE(c.fecha_apertura) BETWEEN :fecha_inicio AND :fecha_fin";
}

$stmtCount = $conn->prepare($sqlCount);
if ($fechaInicio && $fechaFin) {
    $stmtCount->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
    $stmtCount->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
}
$stmtCount->execute();
$totalRegistros = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
$totalPaginas = ceil($totalRegistros / $registrosPorPagina);

// Calculate totals for the period
$sqlTotales = "SELECT 
    SUM(c.monto_inicial) as total_inicial,
    SUM(c.monto_final) as total_final,
    SUM(
        COALESCE(
            (SELECT SUM(dv.subtotal)
             FROM detalle_venta dv
             JOIN ventas v ON dv.venta_id = v.id
             WHERE DATE(v.fecha) = DATE(c.fecha_apertura)
            ), 0
        )
    ) as total_ventas,
    SUM(
        COALESCE(
            (SELECT SUM(dc.subtotal)
             FROM detalle_compras dc
             JOIN compras com ON dc.compra_id = com.id
             WHERE DATE(com.fecha) = DATE(c.fecha_apertura)
            ), 0
        )
    ) as total_compras
FROM caja c 
WHERE c.estado = 'cerrada'";

if ($fechaInicio && $fechaFin) {
    $sqlTotales .= " AND DATE(c.fecha_apertura) BETWEEN :fecha_inicio AND :fecha_fin";
}

$stmtTotales = $conn->prepare($sqlTotales);
if ($fechaInicio && $fechaFin) {
    $stmtTotales->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
    $stmtTotales->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
}
$stmtTotales->execute();
$totales = $stmtTotales->fetch(PDO::FETCH_ASSOC);

// Asegúrate de que no sean NULL antes de realizar la operación
$total_ventas = isset($totales['total_ventas']) ? $totales['total_ventas'] : 0;
$total_compras = isset($totales['total_compras']) ? $totales['total_compras'] : 0;
$monto_inicial = isset($totales['total_inicial']) ? $totales['total_inicial'] : 0;
$monto_final = isset($totales['total_final']) ? $totales['total_final'] : 0;

// Realiza la operación
$diferencia = $monto_final - ($monto_inicial + $total_ventas - $total_compras);

// Si 'diferencia' es un campo que debe actualizarse en la base de datos, puedes hacer lo siguiente:
$sqlUpdate = "UPDATE caja SET diferencia = :diferencia WHERE estado = 'cerrada' AND fecha_apertura BETWEEN :fecha_inicio AND :fecha_fin";
$stmt = $conn->prepare($sqlUpdate);
$stmt->execute([
    ':diferencia' => $diferencia,
    ':fecha_inicio' => $fechaInicio,
    ':fecha_fin' => $fechaFin
]);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Completo de Caja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
    <!-- Enlaza a la CDN de Bootstrap -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Enlaza a la CDN de FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
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

        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .card-header {
            background-color: #34495e;
            color: white;
            border-radius: 8px 8px 0 0 !important;
        }

        .info-box {
            background-color: #fff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.03);
        }

        .table thead th {
            background-color: #34495e;
            color: white;
        }

        .diferencia-positiva {
            color: #27ae60;
            font-weight: 600;
        }

        .diferencia-negativa {
            color: #e74c3c;
            font-weight: 600;
        }

        .pagination {
            margin-bottom: 0;
        }

        .filter-form {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container my-4">
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            <i class="fas fa-history me-2"></i>Historial Completo de Caja
        </h4>
    </div>

    <div class="card-body">
        <!-- Botón de Volver -->
        <div class="mb-3">
            <a href="cierre_caja.php" class="btn btn-outline-light" style="padding: 10px 20px; color: #000; background-color: #fff; border: 1px solid #ccc;">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
        </div>

        <!-- Filtros -->
        <form class="filter-form row g-3 mb-4">
            <div class="col-md-4">
                <label class="form-label"><i class="fas fa-calendar me-2"></i>Fecha Inicio</label>
                <input type="date" name="fecha_inicio" class="form-control" value="<?= $fechaInicio ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label"><i class="fas fa-calendar me-2"></i>Fecha Fin</label>
                <input type="date" name="fecha_fin" class="form-control" value="<?= $fechaFin ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-2"></i>Filtrar
                </button>
                <a href="?" class="btn btn-outline-secondary">
                    <i class="fas fa-undo me-2"></i>Limpiar
                </a>
            </div>
        </form>

            

            <!-- Resumen de totales -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="info-box">
                        <h6><i class="fas fa-sign-in-alt me-2"></i>Total Inicial</h6>
                        <p class="text-primary">$<?= number_format($totales['total_inicial'], 2) ?></p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <h6><i class="fas fa-shopping-cart me-2"></i>Total Ventas</h6>
                        <p class="text-success">$<?= number_format($totales['total_ventas'], 2) ?></p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <h6><i class="fas fa-truck me-2"></i>Total Compras</h6>
                        <p class="text-danger">$<?= number_format($totales['total_compras'], 2) ?></p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <h6><i class="fas fa-cash-register me-2"></i>Total Final</h6>
                        <p class="text-info">$<?= number_format($totales['total_final'], 2) ?></p>
                    </div>
                </div>
            </div>

            <!-- Tabla de resultados -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th><i class="fas fa-calendar me-1"></i>Fecha</th>
                            <th><i class="fas fa-sign-in-alt me-1"></i>Inicial</th>
                            <th><i class="fas fa-shopping-cart me-1"></i>Ventas</th>
                            <th><i class="fas fa-truck me-1"></i>Compras</th>
                            <th><i class="fas fa-cash-register me-1"></i>Final</th>
                            <th><i class="fas fa-balance-scale me-1"></i>Diferencia</th>
                            <th><i class="fas fa-cogs me-1"></i>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cierres as $cierre): 
                            $diferencia = $cierre['monto_final'] - $cierre['monto_inicial'];
                        ?>
                            <tr>
                                <td>
                                    <span class="d-block"><?= date("d/m/Y", strtotime($cierre['fecha_apertura'])) ?></span>
                                    <small class="text-muted">
                                        <?= date("H:i", strtotime($cierre['fecha_apertura'])) ?> - 
                                        <?= date("H:i", strtotime($cierre['fecha_cierre'])) ?>
                                    </small>
                                </td>
                                <td>$<?= number_format($cierre['monto_inicial'], 2) ?></td>
                                <td>$<?= number_format($cierre['total_ventas'] ?? 0, 2) ?></td>
                                <td>$<?= number_format($cierre['total_compras'] ?? 0, 2) ?></td>
                                <td>$<?= number_format($cierre['monto_final'], 2) ?></td>
                                <td>
                                    <span class="<?= ($diferencia >= 0) ? 'diferencia-positiva' : 'diferencia-negativa' ?>">
                                        $<?= number_format($diferencia, 2) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="detalle_cierre.php?id=<?= $cierre['id'] ?>" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye me-1"></i>Ver Detalle
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
<!-- Paginación -->
<?php if ($totalPaginas > 1): ?>
            <div class="d-flex justify-content-between align-items-center mt-4">
                <p class="mb-0">Mostrando <?= count($cierres) ?> de <?= $totalRegistros ?> registros</p>
                <nav aria-label="Page navigation">
                    <ul class="pagination mb-0">
                        <?php if ($pagina > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?pagina=<?= ($pagina-1) ?><?= ($fechaInicio && $fechaFin) ? "&fecha_inicio=$fechaInicio&fecha_fin=$fechaFin" : "" ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php
                        $rangoInicio = max(1, $pagina - 2);
                        $rangoFin = min($totalPaginas, $pagina + 2);
                        
                        if ($rangoInicio > 1) {
                            echo '<li class="page-item"><a class="page-link" href="?pagina=1'.($fechaInicio && $fechaFin ? "&fecha_inicio=$fechaInicio&fecha_fin=$fechaFin" : "").'">1</a></li>';
                            if ($rangoInicio > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }
                        
                        for ($i = $rangoInicio; $i <= $rangoFin; $i++) {
                            echo '<li class="page-item '.($i == $pagina ? 'active' : '').'">
                                    <a class="page-link" href="?pagina='.$i.($fechaInicio && $fechaFin ? "&fecha_inicio=$fechaInicio&fecha_fin=$fechaFin" : "").'">'.$i.'</a>
                                  </li>';
                        }
                        
                        if ($rangoFin < $totalPaginas) {
                            if ($rangoFin < $totalPaginas - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="?pagina='.$totalPaginas.($fechaInicio && $fechaFin ? "&fecha_inicio=$fechaInicio&fecha_fin=$fechaFin" : "").'">'.$totalPaginas.'</a></li>';
                        }
                        ?>
                        
                        <?php if ($pagina < $totalPaginas): ?>
                        <li class="page-item">
                            <a class="page-link" href="?pagina=<?= ($pagina+1) ?><?= ($fechaInicio && $fechaFin) ? "&fecha_inicio=$fechaInicio&fecha_fin=$fechaFin" : "" ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
<script src="../../assets/bootstrap.bundle.min.js"></script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Script para mantener los filtros al recargar la página
    document.querySelectorAll('input[type="date"]').forEach(input => {
        input.addEventListener('change', () => {
            if (input.value) {
                sessionStorage.setItem(input.name, input.value);
            } else {
                sessionStorage.removeItem(input.name);
            }
        });
        
        const savedValue = sessionStorage.getItem(input.name);
        if (savedValue) {
            input.value = savedValue;
        }
    });
</script>
</body>
</html>