<?php
// detalle_cierre.php
session_start();
require_once "../db.php";

// Verificar que se reciba el parámetro id
if (!isset($_GET['id'])) {
    header("Location: cierre_caja.php");
    exit();
}

$id = $_GET['id'];

// Obtener detalles del cierre (ventas y compras)
$sql = "SELECT c.*, 
            (SELECT SUM(dv.subtotal) FROM detalle_venta dv 
             JOIN ventas v ON dv.venta_id = v.id 
             WHERE DATE(v.fecha) = DATE(c.fecha_apertura)) as total_ventas,
            (SELECT SUM(dc.subtotal) FROM detalle_compras dc 
             JOIN compras com ON dc.compra_id = com.id 
             WHERE DATE(com.fecha) = DATE(c.fecha_apertura)) as total_compras
        FROM caja c 
        WHERE c.id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $id]);
$cierre = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener ventas del día
$sqlVentas = "SELECT v.*, c.nombre as cliente, u.nombre as vendedor, 
                  SUM(dv.subtotal) as total
              FROM ventas v 
              LEFT JOIN clientes c ON v.cliente_id = c.id
              LEFT JOIN usuarios u ON v.usuario_id = u.id
              LEFT JOIN detalle_venta dv ON v.id = dv.venta_id
              WHERE DATE(v.fecha) = DATE(:fecha)
              GROUP BY v.id";
$stmt = $conn->prepare($sqlVentas);
$stmt->execute([':fecha' => $cierre['fecha_apertura']]);
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener compras del día
$sqlCompras = "SELECT co.*, p.nombre as proveedor, u.nombre as comprador,
                   SUM(dc.subtotal) as total
              FROM compras co
              LEFT JOIN proveedores p ON co.proveedor_id = p.id
              LEFT JOIN usuarios u ON co.usuario_id = u.id
              LEFT JOIN detalle_compras dc ON co.id = dc.compra_id
              WHERE DATE(co.fecha) = DATE(:fecha)
              GROUP BY co.id";
$stmt = $conn->prepare($sqlCompras);
$stmt->execute([':fecha' => $cierre['fecha_apertura']]);
$compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular el total de compras
$totalCompras = 0;
foreach ($compras as $compra) {
    $totalCompras += $compra['total'];
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Cierre de Caja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Mismos estilos que cierre_caja.php -->
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container my-4">
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">
                <i class="fas fa-receipt me-2"></i>Detalle de Cierre - <?= date("d/m/Y", strtotime($cierre['fecha_apertura'])) ?>
            </h4>
            <a href="cierre_caja.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
        </div>
        <div class="card-body">
            <!-- Resumen del cierre -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="info-box">
                        <h6>Monto Inicial</h6>
                        <p class="text-primary">$<?= number_format($cierre['monto_inicial'], 2) ?></p>
                    </div>
                </div>
                <div class="col-md-3">
                <div class="info-box">
                    <h6>Monto Final</h6>
                    <p class="text-info">$<?= number_format($cierre['monto_final'], 2) ?></p>
                </div>
            </div>
                <div class="col-md-3">
                    <div class="info-box">
                        <h6>Total Compras</h6>
                        <p class="text-danger">$<?= number_format($cierre['total_compras'], 2) ?></p>
                    </div>
                </div>
                <div class="col-md-3">
                <div class="info-box">
                    <h6>Total Compras</h6>
                    <p class="text-success">$<?= number_format($totalCompras, 2) ?></p>
                </div>
            </div>
            </div>

            <!-- Detalles de ventas -->
            <h5 class="mb-3"><i class="fas fa-shopping-cart me-2"></i>Ventas del Día</h5>
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Hora</th>
                            <th>Cliente</th>
                            <th>Vendedor</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ventas as $venta): ?>
                        <tr>
                            <td><?= date("H:i", strtotime($venta['fecha'])) ?></td>
                            <td><?= htmlspecialchars($venta['cliente']) ?></td>
                            <td><?= htmlspecialchars($venta['vendedor']) ?></td>
                            <td>$<?= number_format($venta['total'], 2) ?></td>
                            <td>
                                <a href="detalle_venta.php?id=<?= $venta['id'] ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Detalles de compras -->
            <h5 class="mb-3"><i class="fas fa-truck me-2"></i>Compras del Día</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Hora</th>
                            <th>Proveedor</th>
                            <th>Comprador</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($compras as $compra): ?>
                        <tr>
                            <td><?= date("H:i", strtotime($compra['fecha'])) ?></td>
                            <td><?= htmlspecialchars($compra['proveedor']) ?></td>
                            <td><?= htmlspecialchars($compra['comprador']) ?></td>
                            <td>$<?= number_format($compra['total'], 2) ?></td>
                            <td>
                                <a href="detalle_compra.php?id=<?= $compra['id'] ?>" class="btn btn-sm btn-info">
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

<?php include 'footer.php'; ?>
<script src="../../assets/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>