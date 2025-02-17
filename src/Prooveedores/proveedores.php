<?php
require_once "../../db.php"; // Asegúrate de tener la conexión a la base de datos

// Obtener todos los proveedores
$stmt = $conn->prepare("SELECT id, nombre, telefono, correo, created_at FROM proveedores ORDER BY created_at DESC");
$stmt->execute();
$proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Proveedores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../style/style.css">
</head>
<body>

<?php include '../navbar.php'; ?>

<div class="container mt-5">
    <h1>Listado de Proveedores</h1>

    <!-- Botón para agregar un nuevo proveedor -->
    <a href="proveedor_nuevo.php" class="btn btn-success mb-3">Agregar Nuevo Proveedor</a>

    <!-- Tabla de proveedores -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Teléfono</th>
                <th>Correo</th>
                <th>Fecha de Creación</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($proveedores as $proveedor): ?>
                <tr>
                    <td><?php echo $proveedor['id']; ?></td>
                    <td><?php echo $proveedor['nombre']; ?></td>
                    <td><?php echo $proveedor['telefono']; ?></td>
                    <td><?php echo $proveedor['correo']; ?></td>
                    <td><?php echo $proveedor['created_at']; ?></td>
                    <td>
                        <a href="proveedor_editar.php?id=<?php echo $proveedor['id']; ?>" class="btn btn-warning">Editar</a>
                        <a href="proveedor_detalle.php?id=<?php echo $proveedor['id']; ?>" class="btn btn-info">Ver Detalles</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../footer.php'; ?>
<script src="../../assets/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
