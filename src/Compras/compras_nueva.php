<?php
require_once "../../db.php";

// Obtener todos los proveedores
$stmt = $conn->prepare("SELECT id, nombre FROM proveedores");
$stmt->execute();
$proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener todos los productos
$stmt = $conn->prepare("SELECT id, nombre FROM productos");
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del formulario
    $proveedor_id = $_POST['proveedor_id'];
    $total = $_POST['total'];
    $fecha = date("Y-m-d H:i:s");

    // Verificar si el proveedor existe
    $stmt = $conn->prepare("SELECT COUNT(*) FROM proveedores WHERE id = ?");
    $stmt->execute([$proveedor_id]);
    $proveedor_existe = $stmt->fetchColumn();

    if (!$proveedor_existe) {
        die("El proveedor seleccionado no existe.");
    }

    // Guardar la compra
    $stmt = $conn->prepare("INSERT INTO compras (usuario_id, proveedor_id, total, fecha) 
                            VALUES (?, ?, ?, ?)");
    $stmt->execute([$usuario_id, $proveedor_id, $total, $fecha]);

    $compra_id = $conn->lastInsertId();

    // Guardar los detalles de la compra
    foreach ($_POST['productos'] as $producto) {
        $producto_id = $producto['id'];
        $cantidad = $producto['cantidad'];
        $precio_unitario = $producto['precio_unitario'];
        $subtotal = $cantidad * $precio_unitario;

        $stmt = $conn->prepare("INSERT INTO detalle_compras (compra_id, producto_id, cantidad, precio_unitario, subtotal) 
                                VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$compra_id, $producto_id, $cantidad, $precio_unitario, $subtotal]);
    }

    header("Location: compras_listar.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Nueva Compra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../style/style.css">
</head>
<body>
    
<?php include '../navbar.php'; ?> <!-- Asegúrate de tener una barra de navegación -->

    <div class="container mt-5">
        <h1>Registrar Nueva Compra</h1>
        <form method="POST">
            <div class="mb-3">
                <label for="proveedor_id" class="form-label">Proveedor</label>
                <select class="form-select" id="proveedor_id" name="proveedor_id" required>
                    <?php foreach ($proveedores as $proveedor): ?>
                        <option value="<?php echo $proveedor['id']; ?>"><?php echo $proveedor['nombre']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <h4>Productos</h4>
            <div id="productos">
                <!-- Aquí agregarías los campos para los productos con JavaScript -->
                <div class="mb-3">
                    <label for="producto" class="form-label">Producto</label>
                    <select class="form-select" name="productos[0][id]" required>
                        <?php foreach ($productos as $producto): ?>
                            <option value="<?php echo $producto['id']; ?>"><?php echo $producto['nombre']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="cantidad" class="form-label">Cantidad</label>
                    <input type="number" class="form-control" name="productos[0][cantidad]" required>
                    <label for="precio_unitario" class="form-label">Precio Unitario</label>
                    <input type="number" step="0.01" class="form-control" name="productos[0][precio_unitario]" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="total" class="form-label">Total</label>
                <input type="number" step="0.01" class="form-control" id="total" name="total" required readonly>
            </div>

            <button type="submit" class="btn btn-primary">Registrar Compra</button>
        </form>
    </div>
    <br>
    <?php include '../footer.php'; ?> <!-- Asegúrate de tener un pie de página -->
    <script src="../../assets/bootstrap.bundle.min.js"></script>
    <script>
    // Función para actualizar el total de la compra
    function calcularTotal() {
        let total = 0;
        const productos = document.querySelectorAll('[name^="productos["]'); // Seleccionar todos los campos de productos

        productos.forEach(function(producto) {
            if (producto.name.includes("[cantidad]")) {
                const cantidad = parseFloat(producto.value) || 0; // Obtener cantidad (default 0 si no es un número)
                const precioUnitario = parseFloat(producto.closest('.mb-3').querySelector('[name^="productos["][name$="[precio_unitario]"]').value) || 0; // Obtener precio unitario (default 0 si no es un número)
                const subtotal = cantidad * precioUnitario; // Calcular subtotal
                total += subtotal; // Sumar el subtotal al total
            }
        });

        // Mostrar el total
        document.getElementById('total').value = total.toFixed(2); // Establecer el total en el campo correspondiente
    }

    // Escuchar los cambios en los campos de cantidad y precio
    document.addEventListener('DOMContentLoaded', function() {
        const cantidadInputs = document.querySelectorAll('[name^="productos["][name$="[cantidad]"]');
        const precioInputs = document.querySelectorAll('[name^="productos["][name$="[precio_unitario]"]');

        // Agregar evento de cambio a los inputs de cantidad y precio unitario
        cantidadInputs.forEach(function(input) {
            input.addEventListener('input', calcularTotal);
        });

        precioInputs.forEach(function(input) {
            input.addEventListener('input', calcularTotal);
        });
    });
</script>

</body>
</html>
