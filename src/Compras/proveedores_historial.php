<?php
require_once "../../db.php";

if (!isset($_GET['id'])) {
    header('Location: proveedores.php');
    exit;
}

$id = $_GET['id'];

// Obtener información del proveedor
$stmt = $conn->prepare("SELECT * FROM proveedores WHERE id = ?");
$stmt->execute([$id]);
$proveedor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$proveedor) {
    header('Location: proveedores.php');
    exit;
}

// Obtener historial de compras
$stmt = $conn->prepare("SELECT c.*, 
                              u.nombre as usuario_nombre
                       FROM compras c
                       LEFT JOIN usuarios u ON c.usuario_id = u.id
                       WHERE c.proveedor_id = ?
                       ORDER BY c.fecha DESC");
$stmt->execute([$id]);
$compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular estadísticas
$total_compras = count($compras);
$monto_total = array_sum(array_column($compras, 'total'));
$promedio_compra = $total_compras > 0 ? $monto_total / $total_compras : 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Proveedor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../navbar.php'; ?>

    <div class="page-header">
        <div class="container">
            <h2 class="text-center page-title">
                <i class="fas fa-history me-2"></i>Historial de Proveedor
            </h2>
        </div>
    </div>

    <div class="container mb-5">
        <!-- Información del Proveedor -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="mb-0">
                    <i class="fas fa-building me-2"></i><?= htmlspecialchars($proveedor['nombre']) ?>
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <p><strong>Teléfono:</strong> <?= htmlspecialchars($proveedor['telefono']) ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Email:</strong> <?= htmlspecialchars($proveedor['correo']) ?></p>
                    </div>
                    <div class="col-md-4">
                        <p><strong>Dirección:</strong> <?= htmlspecialchars($proveedor['direccion']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Compras</h5>
                        <p class="card-text display-6"><?= $total_compras ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Monto Total</h5>
                        <p class="card-text display-6">$<?= number_format($monto_total, 2) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Promedio por Compra</h5>
                        <p class="card-text display-6">$<?= number_format($promedio_compra, 2) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historial de Compras -->
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-list me-2"></i>Historial de Compras</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Número</th>
                                <th>Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($compras as $compra): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($compra['fecha'])) ?></td>
                                    <td><?= htmlspecialchars($compra['id']) ?></td>
                                    <td class="text-end">
                                        $<?= number_format($compra['total'], 2) ?>
                                    </td>
                                    <td>
                                        <a href="../compras/compras_ver.php?id=<?= $compra['id'] ?>" 
                                           class="btn btn-primary btn-sm">
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

        <div class="mt-3">
            <a href="proveedores.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>
        </div>
    </div>

    <?php include '../footer.php'; ?>
    <script src="../../assets/bootstrap.bundle.min.js"></script>
</body>
</html>