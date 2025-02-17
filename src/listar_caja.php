<?php
session_start();
require_once "../db.php"; // Conexión a la base de datos

// Verificar si la conexión está establecida
if (!isset($conn)) {
    die("Error: No se pudo conectar a la base de datos.");
}

// Consultar el estado de la caja
$sql = "SELECT * FROM caja ORDER BY id DESC LIMIT 1";
$stmt = $conn->query($sql);
$caja = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

// Consultar los cierres de caja de días anteriores
$sqlCierres = "SELECT * FROM caja WHERE estado = 'cerrada' ORDER BY fecha_cierre DESC";
$stmtCierres = $conn->query($sqlCierres);
$cierres = $stmtCierres->fetchAll(PDO::FETCH_ASSOC);

// Consultar ventas y compras solo si la caja está abierta
$ventas = 0;
$compras = 0;
$efectivoEsperado = 0;
if ($caja && $caja['estado'] == 'abierta') {
    // Obtener las ventas del día sumando los subtotales de detalle_venta
    $sqlVentas = "SELECT SUM(subtotal) AS total_ventas 
                  FROM detalle_venta dv
                  INNER JOIN ventas v ON dv.venta_id = v.id
                  WHERE DATE(v.fecha) = CURDATE()";
    $ventasData = $conn->query($sqlVentas)->fetch(PDO::FETCH_ASSOC);
    $ventas = $ventasData['total_ventas'] ?? 0;

    // Obtener las compras del día sumando los subtotales de detalle_compras
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
    // Verificar si ya hay un cierre de caja para hoy
    $sqlVerificar = "SELECT * FROM caja WHERE DATE(fecha_apertura) = CURDATE() AND estado = 'abierta'";
    $stmtVerificar = $conn->query($sqlVerificar);
    if ($stmtVerificar->rowCount() > 0) {
        // Si ya hay una caja abierta, no permitir abrir otra
        echo "<script>alert('Ya existe una caja abierta para hoy.');</script>";
    } else {
        $montoInicial = $_POST['monto_inicial'];
        $sql = "INSERT INTO caja (monto_inicial, estado, fecha_apertura) VALUES (:monto_inicial, 'abierta', NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':monto_inicial' => $montoInicial]);
        header("Location: cierre_caja.php");
        exit();
    }
}

// Cierre de caja
if (isset($_POST['cerrar_caja'])) {
    $montoFinal = $_POST['monto_final'];

    // Actualizamos solo el monto final y el estado de la caja
    $sql = "UPDATE caja SET 
            monto_final = :monto_final, 
            estado = 'cerrada', 
            fecha_cierre = NOW() 
            WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':monto_final' => $montoFinal, 
        ':id' => $caja['id']
    ]);
    header("Location: cierre_caja.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
    <title>Gestionar Caja</title>
</head>
<?php include 'navbar.php'; ?>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="text-center">Gestión de Caja</h2>
    
    <!-- Listado de cierres de caja anteriores -->
    <div class="card p-4 shadow-lg">
        <h4 class="text-center">Cierres de Caja Anteriores</h4>
        <?php if (count($cierres) > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Fecha de Cierre</th>
                        <th>Monto Inicial</th>
                        <th>Total Ventas</th>
                        <th>Total Compras</th>
                        <th>Monto Final</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cierres as $cierre): ?>
                        <tr>
                            <td><?= date("d-m-Y H:i:s", strtotime($cierre['fecha_cierre'])) ?></td>
                            <td>$<?= number_format($cierre['monto_inicial'], 2) ?></td>
                            <td>$<?= number_format($ventas, 2) ?></td> <!-- Se muestra el total de ventas calculado -->
                            <td>$<?= number_format($compras, 2) ?></td> <!-- Se muestra el total de compras calculado -->
                            <td>$<?= number_format($cierre['monto_final'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">No hay cierres de caja anteriores.</p>
        <?php endif; ?>
    </div>

    <br>

    <!-- Apertura de caja -->
    <?php if (!$caja || $caja['estado'] == 'cerrada'): ?>
        <div class="card p-4 shadow-lg">
            <h4 class="text-center">Apertura de Caja</h4>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Monto Inicial</label>
                    <input type="number" name="monto_inicial" class="form-control" required>
                </div>
                <button type="submit" name="abrir_caja" class="btn btn-success w-100">Abrir Caja</button>
            </form>
        </div>
    <?php else: ?>
        <div class="card p-4 shadow-lg">
            <h4 class="text-center">Cierre de Caja</h4>
            <p><strong>Monto Inicial:</strong> $<?= number_format($caja['monto_inicial'], 2) ?></p>
            <p><strong>Ventas del Día:</strong> $<?= number_format($ventas, 2) ?></p>
            <p><strong>Compras del Día:</strong> $<?= number_format($compras, 2) ?></p>
            <p><strong>Efectivo Esperado:</strong> $<?= number_format($efectivoEsperado, 2) ?></p>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Monto en Caja al Cierre</label>
                    <input type="number" name="monto_final" class="form-control" required>
                </div>
                <button type="submit" name="cerrar_caja" class="btn btn-danger w-100">Cerrar Caja</button>
            </form>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
<script src="../assets/bootstrap.bundle.min.js"></script>
</body>
</html>
