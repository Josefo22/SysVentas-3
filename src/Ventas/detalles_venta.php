<?php
require_once "../../db.php"; // Conexión a la base de datos

// Verificar si se ha pasado un ID de venta
if (!isset($_GET['venta_id'])) {
    die("ID de venta no proporcionado.");
}

$venta_id = $_GET['venta_id'];

// Obtener los detalles de la venta
$stmt = $conn->prepare("SELECT v.id, v.total, c.nombre AS cliente, v.fecha 
                        FROM ventas v 
                        JOIN clientes c ON v.cliente_id = c.id 
                        WHERE v.id = ?");
$stmt->execute([$venta_id]);
$venta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$venta) {
    die("Venta no encontrada.");
}

// Obtener los detalles de los productos vendidos
$stmt = $conn->prepare("SELECT p.nombre, d.cantidad, d.precio, d.subtotal 
                        FROM detalle_venta d 
                        JOIN productos p ON d.producto_id = p.id 
                        WHERE d.venta_id = ?");
$stmt->execute([$venta_id]);
$detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles de la Venta</title>
    <link rel="stylesheet" href="../../style/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include '../navbar.php'; ?>

    <div class="container mt-5">
        <h1 class="text-center mb-4">Detalles de la Venta</h1>

        <!-- Información de la venta -->
        <div class="mb-4">
            <h3>Venta #<?php echo $venta['id']; ?></h3>
            <p><strong>Cliente:</strong> <?php echo $venta['cliente']; ?></p>
            <p><strong>Fecha:</strong> <?php echo $venta['fecha']; ?></p>
            <p><strong>Total:</strong> <?php echo number_format($venta['total'], 2); ?></p>
        </div>

        <!-- Tabla con los productos vendidos -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detalles as $item): ?>
                        <tr>
                            <td><?php echo $item['nombre']; ?></td>
                            <td><?php echo $item['cantidad']; ?></td>
                            <td><?php echo number_format($item['precio'], 2); ?></td>
                            <td><?php echo number_format($item['subtotal'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Botón para regresar al listado de ventas -->
        <div class="mt-4">
            <a href="ventas_listar.php" class="btn btn-secondary">Regresar al Listado de Ventas</a>
        </div>
    </div>

    <?php include '../footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/bootstrap.bundle.min.js"></script>
</body>
</html>
