<?php
require_once "../../db.php"; // Conexión a la base de datos

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        echo "<div class='alert alert-danger text-center'>Producto no encontrado.</div>";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cantidad = $_POST['cantidad'];

    $stmt = $conn->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
    $stmt->execute([$cantidad, $id]);

    header("Location: productos.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Añadir Stock</title>
    <link rel="stylesheet" href="../../style/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include '../navbar.php'; ?>
<div class="container mt-5">
    <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-header bg-primary text-white text-center">
            <h4>Añadir Stock</h4>
        </div>
        <div class="card-body">
            <p><strong>Producto:</strong> <?= htmlspecialchars($producto['nombre']) ?></p>
            <p><strong>Stock Actual:</strong> <?= $producto['stock'] ?></p>

            <form method="post">
                <div class="mb-3">
                    <label for="cantidad" class="form-label">Cantidad a añadir:</label>
                    <input type="number" name="cantidad" class="form-control" min="1" required>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success">Añadir Stock</button>
                    <a href="productos.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include '../footer.php'; ?>
<script src="../../assets/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
