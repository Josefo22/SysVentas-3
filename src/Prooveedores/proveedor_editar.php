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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar datos del formulario
    $nombre = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $correo = $_POST['correo'];

    // Actualizar proveedor en la base de datos
    $stmt = $conn->prepare("UPDATE proveedores SET nombre = ?, telefono = ?, direccion = ?, correo = ? WHERE id = ?");
    $stmt->execute([$nombre, $telefono, $direccion, $correo, $id]);

    header('Location: proveedores.php'); // Redirigir al listado de proveedores
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Proveedor</title>
    <link rel="stylesheet" href="../../style/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include '../navbar.php'; ?>

<div class="container mt-5">
<form action=".\proveedores.php">
            <button type="submit" class="btn btn-primary mb-3">Volver</button>
        </form>
    <h1>Editar Proveedor</h1>
    

    <form action="proveedor_editar.php?id=<?php echo $proveedor['id']; ?>" method="POST">
        <div class="mb-3">
            <label for="nombre" class="form-label">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo $proveedor['nombre']; ?>" required>
        </div>
        <div class="mb-3">
            <label for="telefono" class="form-label">Teléfono</label>
            <input type="text" class="form-control" id="telefono" name="telefono" value="<?php echo $proveedor['telefono']; ?>">
        </div>
        <div class="mb-3">
            <label for="direccion" class="form-label">Dirección</label>
            <input type="text" class="form-control" id="direccion" name="direccion" value="<?php echo $proveedor['direccion']; ?>">
        </div>
        <div class="mb-3">
            <label for="correo" class="form-label">Correo</label>
            <input type="email" class="form-control" id="correo" name="correo" value="<?php echo $proveedor['correo']; ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Actualizar Proveedor</button>
    </form>
</div>
<br>
<?php include '../footer.php'; ?>
<script src="../../assets/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
