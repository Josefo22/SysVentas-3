<?php
require_once "../../db.php"; // Asegúrate de tener la conexión a la base de datos

if (!isset($_GET['id'])) {
    die("ID de proveedor no proporcionado.");
}

$id = $_GET['id'];

// Obtener los datos del proveedor
$stmt = $conn->prepare("SELECT * FROM proveedores WHERE id = ?");
$stmt->execute([$id]);
$proveedor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$proveedor) {
    die("Proveedor no encontrado.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Proveedor</title>
    <link rel="stylesheet" href="../../style/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include '../navbar.php'; ?>

<div class="container mt-5">
    <h1>Detalles del Proveedor</h1>

    <table class="table">
        <tr>
            <th>Nombre</th>
            <td><?php echo $proveedor['nombre']; ?></td>
        </tr>
        <tr>
            <th>Teléfono</th>
            <td><?php echo $proveedor['telefono']; ?></td>
        </tr>
        <tr>
            <th>Dirección</th>
            <td><?php echo $proveedor['direccion']; ?></td>
        </tr>
        <tr>
            <th>Correo</th>
            <td><?php echo $proveedor['correo']; ?></td>
        </tr>
        <tr>
            <th>Fecha de Creación</th>
            <td><?php echo $proveedor['created_at']; ?></td>
        </tr>
    </table>

    <a href="proveedores.php" class="btn btn-secondary">Volver al Listado de Proveedores</a>
</div>

<?php include '../footer.php'; ?>
<script src="../../assets/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
