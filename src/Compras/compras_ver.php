<?php
require_once "../../db.php";

if (!isset($_GET['id'])) {
    header('Location: compras.php');
    exit;
}

$id = $_GET['id'];

try {
    // Verificar si la tabla compras_detalle existe antes de consultar
    $stmt = $conn->query("SHOW TABLES LIKE 'detalle_compras'");
    if ($stmt->rowCount() === 0) {
        throw new Exception("Error: La tabla 'compras_detalle' no existe.");
    }

    // Obtener información de la compra
    $stmt = $conn->prepare("SELECT c.*, 
                                  p.nombre as proveedor_nombre,
                                  p.telefono as proveedor_telefono,
                                  p.correo as proveedor_correo,
                                  u.nombre as usuario_nombre
                           FROM compras c
                           LEFT JOIN proveedores p ON c.proveedor_id = p.id
                           LEFT JOIN usuarios u ON c.usuario_id = u.id
                           WHERE c.id = ?");
    $stmt->execute([$id]);
    $compra = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$compra) {
        header('Location: compras.php');
        exit;
    }

    // Obtener detalles de la compra con la tabla correcta 'compras_detalle'
    $stmt = $conn->prepare("SELECT cd.*, p.nombre AS producto_nombre, cd.cantidad * p.precio AS subtotal
                            FROM detalle_compras cd
                            LEFT JOIN productos p ON cd.producto_id = p.id
                            WHERE cd.compra_id = ?");
    $stmt->execute([$id]);
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calcular totales
    $subtotal = array_sum(array_column($detalles, 'subtotal'));

} catch (Exception $e) {
    die("<div class='alert alert-danger text-center mt-3'>" . $e->getMessage() . "</div>");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de Compra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../navbar.php'; ?>
<br>
    <div class="page-header">
        <div class="container">
            <h2 class="text-center page-title">
                <i class="fas fa-file-invoice me-2"></i>Detalles de Compra
            </h2>
        </div>
    </div>

    <div class="container mb-5">
        <!-- Información de la Compra -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="mb-0">
                    <i class="fas fa-shopping-cart me-2"></i>Compra #<?= htmlspecialchars($compra['id']) ?>
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4>Información General</h4>
                        <p><strong>Fecha:</strong> <?= date('d/m/Y', strtotime($compra['fecha'])) ?></p>
                        <p><strong>Total:</strong> $<?= number_format($compra['total'], 2) ?></p>
                    </div>
                    <div class="col-md-6">
                        <h4>Información del Proveedor</h4>
                        <p><strong>Nombre:</strong> <?= htmlspecialchars($compra['proveedor_nombre']) ?></p>
                        <p><strong>Teléfono:</strong> <?= htmlspecialchars($compra['proveedor_telefono']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($compra['proveedor_correo']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detalles de la Compra -->
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-list me-2"></i>Productos Comprados</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th class="text-end">Cantidad</th>
                                <th class="text-end">Precio Unitario</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($detalles as $detalle): ?>
                                <tr>
                                    <td><?= htmlspecialchars($detalle['compra_id']) ?></td>
                                    <td><?= htmlspecialchars($detalle['producto_nombre']) ?></td>
                                    <td class="text-end"><?= number_format($detalle['cantidad']) ?></td>
                                    <td class="text-end">$<?= number_format($detalle['precio_unitario'], 2) ?></td>
                                    <td class="text-end">$<?= number_format($detalle['subtotal'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="table-primary">
                                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                <td class="text-end"><strong>$<?= number_format($subtotal, 2) ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <?php if (!empty($compra['notas'])): ?>
                <div class="mt-4">
                    <h4>Notas</h4>
                    <p class="bg-light p-3 rounded"><?= nl2br(htmlspecialchars($compra['notas'])) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-3 d-flex justify-content-between">
            <a href="compras.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Volver
            </a>

        </div>
    </div>

    <?php include '../footer.php'; ?>
    <script src="../../assets/bootstrap.bundle.min.js"></script>
</body>
</html>
