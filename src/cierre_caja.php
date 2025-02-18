<?php
session_start();
require_once "../db.php";

// Verificar si la conexión está establecida
if (!isset($conn)) {
    die("Error: No se pudo conectar a la base de datos.");
}

// Consultar el estado de la caja actual
$sql = "SELECT * FROM caja ORDER BY id DESC LIMIT 1";
$stmt = $conn->query($sql);
$caja = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

// Consultar los cierres de caja anteriores
$sqlCierres = "SELECT c.*, 
              (SELECT SUM(dv.subtotal) FROM detalle_venta dv 
               JOIN ventas v ON dv.venta_id = v.id 
               WHERE DATE(v.fecha) = DATE(c.fecha_apertura)) as total_ventas,
              (SELECT SUM(dc.subtotal) FROM detalle_compras dc 
               JOIN compras com ON dc.compra_id = com.id 
               WHERE DATE(com.fecha) = DATE(c.fecha_apertura)) as total_compras
              FROM caja c 
              WHERE c.estado = 'cerrada' 
              ORDER BY c.fecha_cierre DESC LIMIT 10";
$stmtCierres = $conn->query($sqlCierres);
$cierres = $stmtCierres->fetchAll(PDO::FETCH_ASSOC);

// Variables para la caja actual
$ventas = 0;
$compras = 0;
$efectivoEsperado = 0;

// Obtener datos si la caja está abierta
if ($caja && $caja['estado'] == 'abierta') {
    // Obtener las ventas del día
    $sqlVentas = "SELECT SUM(subtotal) AS total_ventas 
                  FROM detalle_venta dv
                  INNER JOIN ventas v ON dv.venta_id = v.id
                  WHERE DATE(v.fecha) = CURDATE()";
    $ventasData = $conn->query($sqlVentas)->fetch(PDO::FETCH_ASSOC);
    $ventas = $ventasData['total_ventas'] ?? 0;

    // Obtener las compras del día
    $sqlCompras = "SELECT SUM(subtotal) AS total_compras 
                   FROM detalle_compras dc
                   INNER JOIN compras c ON dc.compra_id = c.id
                   WHERE DATE(c.fecha) = CURDATE()";
    $comprasData = $conn->query($sqlCompras)->fetch(PDO::FETCH_ASSOC);
    $compras = $comprasData['total_compras'] ?? 0;

    // Calcular el efectivo esperado
    $efectivoEsperado = $caja['monto_inicial'] + $ventas - $compras;
}

// Apertura de caja
if (isset($_POST['abrir_caja'])) {
    // Verificar si ya hay una caja abierta
    $sqlVerificar = "SELECT * FROM caja WHERE DATE(fecha_apertura) = CURDATE() AND estado = 'abierta'";
    $stmtVerificar = $conn->query($sqlVerificar);
    
    if ($stmtVerificar->rowCount() > 0) {
        $mensaje = "Ya existe una caja abierta para hoy.";
        $tipoMensaje = "danger";
    } else {
        $montoInicial = $_POST['monto_inicial'];
        $observacion = $_POST['observacion'] ?? '';
        
        $sql = "INSERT INTO caja (monto_inicial, estado, fecha_apertura, observaciones) 
                VALUES (:monto_inicial, 'abierta', NOW(), :observacion)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':monto_inicial' => $montoInicial,
            ':observacion' => $observacion
        ]);
        
        header("Location: cierre_caja.php");
        exit();
    }
}

// Cierre de caja
if (isset($_POST['cerrar_caja'])) {
    $montoFinal = $_POST['monto_final'];
    $observacion = $_POST['observacion_cierre'] ?? '';
    $diferencia = $montoFinal - $efectivoEsperado;

    // Modificamos la consulta para no incluir el campo 'diferencia' que no existe
    $sql = "UPDATE caja SET 
            monto_final = :monto_final,
            estado = 'cerrada',
            fecha_cierre = NOW(),
            observaciones = :observacion_cierre
            WHERE id = :id";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':monto_final' => $montoFinal,
        ':observacion_cierre' => $observacion,
        ':id' => $caja['id']
    ]);
    
    $mensaje = "La caja ha sido cerrada exitosamente.";
    $tipoMensaje = "success";
    
    // Recargar la página para mostrar los cambios
    header("Location: cierre_caja.php?mensaje=".urlencode($mensaje)."&tipo=".urlencode($tipoMensaje));
    exit();
}

// Recuperar mensaje de sesión
if (isset($_GET['mensaje'])) {
    $mensaje = $_GET['mensaje'];
    $tipoMensaje = $_GET['tipo'] ?? 'info';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Caja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
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
        
        .table thead th {
            background-color: #34495e;
            color: white;
            padding: 12px;
        }

        .table tbody td {
            padding: 12px;
            vertical-align: middle;
        }
        
        .badge-success {
            background-color: #2ecc71;
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-weight: 500;
        }
        
        .badge-danger {
            background-color: #e74c3c;
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-weight: 500;
        }
        
        .badge-warning {
            background-color: #f39c12;
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-weight: 500;
        }
        
        .badge-info {
            background-color: #3498db;
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-weight: 500;
        }
        
        .caja-status {
            margin-bottom: 15px;
            padding: 15px;
            border-radius: 8px;
        }
        
        .status-abierta {
            background-color: #e8f5e9;
            border-left: 4px solid #2ecc71;
        }
        
        .status-cerrada {
            background-color: #ffebee;
            border-left: 4px solid #e74c3c;
        }
        
        .info-box {
            background-color: #fff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.03);
        }
        
        .info-box h6 {
            color: #7f8c8d;
            margin-bottom: 10px;
        }
        
        .info-box p {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0;
        }
        
        .btn {
            border-radius: 5px;
            padding: 10px 20px;
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
        
        .btn-danger {
            background-color: #e74c3c;
            border: none;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
        }
        
        .diferencia-positiva {
            color: #27ae60;
        }
        
        .diferencia-negativa {
            color: #e74c3c;
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="page-header">
    <div class="container">
        <h2 class="text-center page-title">
            <i class="fas fa-cash-register me-2"></i>Gestión de Caja
        </h2>
    </div>
</div>

<div class="container mb-5">
    <?php if (isset($mensaje)): ?>
    <div class="alert alert-<?= $tipoMensaje ?> alert-dismissible fade show" role="alert">
        <?= $mensaje ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Estado actual de la caja -->
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-cash-register me-2"></i>Estado Actual de Caja</h4>
            <?php if ($caja): ?>
                <?php if ($caja['estado'] == 'abierta'): ?>
                    <span class="badge-success"><i class="fas fa-lock-open me-1"></i>Caja Abierta</span>
                <?php else: ?>
                    <span class="badge-danger"><i class="fas fa-lock me-1"></i>Caja Cerrada</span>
                <?php endif; ?>
            <?php else: ?>
                <span class="badge-warning"><i class="fas fa-exclamation-triangle me-1"></i>No hay cajas registradas</span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <?php if ($caja): ?>
                <div class="caja-status <?= $caja['estado'] == 'abierta' ? 'status-abierta' : 'status-cerrada' ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <p><i class="fas fa-calendar-day me-2"></i><strong>Fecha de apertura:</strong> 
                            <?= date("d/m/Y H:i", strtotime($caja['fecha_apertura'])) ?></p>
                            <p><i class="fas fa-money-bill-wave me-2"></i><strong>Monto inicial:</strong> 
                            $<?= number_format($caja['monto_inicial'], 2) ?></p>
                            <?php if (!empty($caja['observaciones'])): ?>
                                <p><i class="fas fa-comment me-2"></i><strong>Observación:</strong> 
                                <?= htmlspecialchars($caja['observaciones']) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php if ($caja['estado'] == 'cerrada'): ?>
                        <div class="col-md-6">
                            <p><i class="fas fa-calendar-check me-2"></i><strong>Fecha de cierre:</strong> 
                            <?= date("d/m/Y H:i", strtotime($caja['fecha_cierre'])) ?></p>
                            <p><i class="fas fa-money-bill me-2"></i><strong>Monto final:</strong> 
                            $<?= number_format($caja['monto_final'], 2) ?></p>
                            <?php 
                            // Calcular la diferencia en el caso de que no exista la columna
                            $cajaFinal = $caja['monto_final'] ?? 0;
                            $cajaInicial = $caja['monto_inicial'] ?? 0;
                            $diferencia = $cajaFinal - $cajaInicial;
                            ?>
                            <p><i class="fas fa-balance-scale me-2"></i><strong>Diferencia:</strong> 
                            <span class="<?= ($diferencia >= 0) ? 'diferencia-positiva' : 'diferencia-negativa' ?>">
                                $<?= number_format($diferencia, 2) ?>
                            </span>
                            </p>
                            <?php if (!empty($caja['observaciones_cierre'])): ?>
                                <p><i class="fas fa-comment-dots me-2"></i><strong>Observación de cierre:</strong> 
                                <?= htmlspecialchars($caja['observaciones_cierre']) ?></p>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($caja['estado'] == 'abierta'): ?>
                <div class="row mt-4">
                    <div class="col-md-3 mb-3">
                        <div class="info-box">
                            <h6><i class="fas fa-sign-in-alt me-2"></i>Monto Inicial</h6>
                            <p class="text-primary">$<?= number_format($caja['monto_inicial'], 2) ?></p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="info-box">
                            <h6><i class="fas fa-shopping-cart me-2"></i>Ventas del Día</h6>
                            <p class="text-success">$<?= number_format($ventas, 2) ?></p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="info-box">
                            <h6><i class="fas fa-truck me-2"></i>Compras del Día</h6>
                            <p class="text-danger">$<?= number_format($compras, 2) ?></p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="info-box">
                            <h6><i class="fas fa-balance-scale me-2"></i>Efectivo Esperado</h6>
                            <p class="text-info">$<?= number_format($efectivoEsperado, 2) ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Formulario de Cierre de Caja -->
                <div class="card mt-4 border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3"><i class="fas fa-cash-register me-2"></i>Cerrar Caja</h5>
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><i class="fas fa-money-bill me-2"></i>Monto en Caja al Cierre</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" name="monto_final" class="form-control" step="0.01" required>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><i class="fas fa-comment me-2"></i>Observaciones (opcional)</label>
                                    <textarea name="observacion_cierre" class="form-control" rows="1"></textarea>
                                </div>
                            </div>
                            <div class="d-grid gap-2 mt-3">
                                <button type="submit" name="cerrar_caja" class="btn btn-danger">
                                    <i class="fas fa-lock me-2"></i>Cerrar Caja del Día
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-exclamation-circle fa-3x text-warning mb-3"></i>
                    <h5>No hay cajas registradas en el sistema</h5>
                    <p class="text-muted">Realice la apertura de caja para comenzar las operaciones del día.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Apertura de caja (si está cerrada) -->
    <?php if (!$caja || $caja['estado'] == 'cerrada'): ?>
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0"><i class="fas fa-door-open me-2"></i>Apertura de Caja</h4>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-money-bill-wave me-2"></i>Monto Inicial</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="monto_inicial" class="form-control" step="0.01" required>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-comment me-2"></i>Observaciones (opcional)</label>
                        <textarea name="observacion" class="form-control" rows="1"></textarea>
                    </div>
                </div>
                <div class="d-grid gap-2 mt-3">
                    <button type="submit" name="abrir_caja" class="btn btn-success">
                        <i class="fas fa-unlock me-2"></i>Abrir Caja del Día
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Historial de cierres de caja -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-history me-2"></i>Historial de Cierres de Caja</h4>
            <a href="reporte_caja.php" class="btn btn-sm btn-outline-light">
                <i class="fas fa-file-export me-1"></i>Exportar Reporte
            </a>
        </div>
        <div class="card-body">
            <?php if (count($cierres) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-calendar me-1"></i>Fecha</th>
                                <th><i class="fas fa-money-bill-wave me-1"></i>Inicial</th>
                                <th><i class="fas fa-shopping-cart me-1"></i>Ventas</th>
                                <th><i class="fas fa-truck me-1"></i>Compras</th>
                                <th><i class="fas fa-cash-register me-1"></i>Final</th>
                                <th><i class="fas fa-balance-scale me-1"></i>Diferencia</th>
                                <th><i class="fas fa-cogs me-1"></i>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cierres as $cierre): 
                                $diferencia = $cierre['monto_final'] - ($cierre['monto_inicial'] + $cierre['total_ventas'] - $cierre['total_compras']);
                            ?>
                                <tr>
                                    <td>
                                        <span class="d-block"><?= date("d/m/Y", strtotime($cierre['fecha_apertura'])) ?></span>
                                        <small class="text-muted"><?= date("H:i", strtotime($cierre['fecha_apertura'])) ?> - <?= date("H:i", strtotime($cierre['fecha_cierre'])) ?></small>
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
                                            <i class="fas fa-eye me-1"></i>Detalles
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="historial_completo_caja.php" class="btn btn-outline-secondary">
                        <i class="fas fa-list-alt me-2"></i>Ver Historial Completo
                    </a>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No hay cierres de caja anteriores registrados en el sistema.</p>
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
    // Desaparecer alerta después de 5 segundos
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
    
    // Confirmar cierre de caja
    document.querySelector('button[name="cerrar_caja"]').addEventListener('click', function(e) {
        if(!confirm('¿Está seguro que desea cerrar la caja? Esta acción no se puede deshacer.')) {
            e.preventDefault();
        }
    });
</script>
</body>
</html>