<?php
require_once "../../db.php"; // Asegúrate de tener la conexión a la base de datos

// Obtener todas las compras
$stmt = $conn->prepare("SELECT c.id, c.total, p.nombre AS proveedor, c.fecha 
                        FROM compras c 
                        JOIN proveedores p ON c.proveedor_id = p.id 
                        ORDER BY c.fecha DESC");
$stmt->execute();
$compras = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Compras</title>
    <link rel="stylesheet" href="../../style/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../navbar.php'; ?> <!-- Asegúrate de tener una barra de navegación -->

    <div class="container mt-5">
        <h1>Listado de Compras</h1>
        <form action="compras.php" method="get">
            <button type="submit" class="btn btn-primary mb-3">Volver</button>
        </form>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID Compra</th>
                    <th>Proveedor</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($compras as $compra): ?>
                    <tr>
                        <td><?php echo $compra['id']; ?></td>
                        <td><?php echo $compra['proveedor']; ?></td>
                        <td><?php echo $compra['fecha']; ?></td>
                        <td><?php echo number_format($compra['total'], 2); ?></td>
                        <td>
                            <a href="compras_detalle.php?id=<?php echo $compra['id']; ?>" class="btn btn-info">Ver Detalles</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php include '../footer.php'; ?> <!-- Asegúrate de tener un pie de página -->
</body>

<script src="../../assets/bootstrap.bundle.min.js"></script>
</html>
