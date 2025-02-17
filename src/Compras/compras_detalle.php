<?php
require_once "../../db.php";

if (!isset($_GET['id'])) {
    die("ID de compra no proporcionado.");
}

$compra_id = $_GET['id'];

// Obtener datos de la compra
$stmt = $conn->prepare("SELECT c.id, c.total, p.nombre AS proveedor, c.fecha 
                        FROM compras c 
                        JOIN proveedores p ON c.proveedor_id = p.id 
                        WHERE c.id = ?");
$stmt->execute([$compra_id]);
$compra = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$compra) {
    die("Compra no encontrada.");
}

// Obtener detalles de la compra
$stmt = $conn->prepare("SELECT pr.nombre, dc.cantidad, dc.precio_unitario, dc.subtotal 
                        FROM detalle_compras dc 
                        JOIN productos pr ON dc.producto_id = pr.id 
                        WHERE dc.compra_id = ?");
$stmt->execute([$compra_id]);
$detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Compra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../navbar.php'; ?> <!-- Asegúrate de tener una barra de navegación -->

    <div class="container mt-5">
        <h1>Detalle de Compra</h1>
        <p><strong>Proveedor:</strong> <?php echo $compra['proveedor']; ?></p>
        <p><strong>Fecha:</strong> <?php echo $compra['fecha']; ?></p>
        <p><strong>Total:</strong> <?php echo number_format($compra['total'], 2); ?></p>

        <h4>Productos</h4>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $detalle): ?>
                    <tr>
                        <td><?php echo $detalle['nombre']; ?></td>
                        <td><?php echo $detalle['cantidad']; ?></td>
                        <td><?php echo number_format($detalle['precio_unitario'], 2); ?></td>
                        <td><?php echo number_format($detalle['subtotal'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <a href="compras_listar.php" class="btn btn-secondary">Regresar al Listado</a>
    </div>
    <?php include '../footer.php'; ?> <!-- Asegúrate de tener un pie de página -->
</body>
<script src="../../assets/bootstrap.bundle.min.js"></script>
</html>
