<?php
require_once "../../db.php"; // Conexión a la base de datos

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        echo "<div class='alert alert-danger text-center mt-3'>Producto no encontrado.</div>";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $categoria_id = $_POST['categoria_id'];

    $stmt = $conn->prepare("UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, categoria_id = ? WHERE id = ?");
    $stmt->execute([$nombre, $descripcion, $precio, $categoria_id, $id]);

    header("Location: productos.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Producto</title>
    <link rel="stylesheet" href="../../style/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-custom {
            max-width: 500px;
            margin: auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background: white;
        }
    </style>
</head>
<body>
<?php include '../navbar.php'; ?>

<div class="container mt-5">
    <div class="card card-custom">
        <h2 class="text-center">Editar Producto</h2>
        <form method="post" class="mt-3">
            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre del producto:</label>
                <input type="text" id="nombre" name="nombre" class="form-control" value="<?= htmlspecialchars($producto['nombre']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción:</label>
                <textarea id="descripcion" name="descripcion" class="form-control" rows="3" required><?= htmlspecialchars($producto['descripcion']) ?></textarea>
            </div>

            <div class="mb-3">
                <label for="precio" class="form-label">Precio:</label>
                <input type="number" id="precio" name="precio" class="form-control" value="<?= $producto['precio'] ?>" step="0.01" required>
            </div>

            <div class="mb-3">
               <label for="categoria_nombre" class="form-label">Categoría:</label>
               <input type="text" id="categoria_nombre" name="categoria_nombre" class="form-control" value="" required>
               <input type="hidden" id="categoria_id" name="categoria_id" value="<?= $producto['categoria_id'] ?>">
           </div>


            <div class="text-center">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                <a href="productos.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
<br>
<?php include '../footer.php'; ?>
<script src="../../assets/bootstrap.bundle.min.js"></script>
<!-- jQuery y jQuery UI para Autocompletado -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<script>
$(function() {
    $("#categoria_nombre").autocomplete({
        source: "buscar_categorias.php",
        minLength: 2,
        select: function(event, ui) {
            $("#categoria_nombre").val(ui.item.label); // Nombre de la categoría
            $("#categoria_id").val(ui.item.id); // ID de la categoría
            return false;
        }
    });
});
</script>

</body>
</html>
