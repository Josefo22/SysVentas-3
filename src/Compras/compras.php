<?php
require_once "../../db.php"; // Asegúrate de tener la conexión a la base de datos

// Obtener las últimas compras (puedes personalizar la consulta según lo que necesites)
$stmt = $conn->prepare("SELECT c.id, c.total, p.nombre AS proveedor, c.fecha 
                        FROM compras c 
                        JOIN proveedores p ON c.proveedor_id = p.id 
                        ORDER BY c.fecha DESC LIMIT 5");
$stmt->execute();
$compras = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../style/style.css">
</head>
<body>

<?php include '../navbar.php'; ?> <!-- Asegúrate de tener una barra de navegación -->

<div class="container mt-5">
    <h1>Gestión de Compras</h1>
    <p class="lead">Aquí puedes gestionar todas las compras realizadas.</p>

    <!-- Botón para registrar una nueva compra -->
    <form action="compras_nueva.php" method="get">
        <button type="submit" class="btn btn-success mb-3">Registrar Nueva Compra</button>
    </form>

    <!-- Tabla con las últimas compras -->
    <h3>Últimas Compras</h3>
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
                        <!-- Ver detalles de la compra -->
                        <a href="compras_detalle.php?id=<?php echo $compra['id']; ?>" class="btn btn-info">Ver Detalles</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Botón para ver el listado completo de compras -->
    <form action="compras_listar.php" method="get">
        <button type="submit" class="btn btn-primary mt-3">Ver Listado Completo de Compras</button>
    </form>
</div>
<br>
<?php include '../footer.php'; ?> <!-- Asegúrate de tener un pie de página -->
<script src="../../assets/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
