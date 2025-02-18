<?php
require_once "../../db.php";

// Obtener proveedores
$stmt = $conn->prepare("SELECT id, nombre FROM proveedores");
$stmt->execute();
$proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener productos con sus precios
$stmt = $conn->prepare("SELECT id, nombre, precio FROM productos");
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();
        
        $proveedor_id = $_POST['proveedor_id'];
        $total = $_POST['total'];
        $fecha = date('Y-m-d');
        
        // Insertar la compra
        $stmt = $conn->prepare("INSERT INTO compras (proveedor_id, total, fecha) VALUES (?, ?, ?)");
        $stmt->execute([$proveedor_id, $total, $fecha]);
        $compra_id = $conn->lastInsertId();
        
        // Insertar los detalles de la compra
        $stmt = $conn->prepare("INSERT INTO detalle_compra (compra_id, producto_id, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
        
        foreach ($_POST['productos'] as $producto) {
            if (!empty($producto['id']) && !empty($producto['cantidad']) && !empty($producto['precio_unitario'])) {
                $stmt->execute([
                    $compra_id,
                    $producto['id'],
                    $producto['cantidad'],
                    $producto['precio_unitario']
                ]);
                
                // Actualizar el stock del producto
                $stmt_stock = $conn->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
                $stmt_stock->execute([$producto['cantidad'], $producto['id']]);
            }
        }
        
        $conn->commit();
        header("Location: index.php?success=1");
        exit();
        
    } catch (Exception $e) {
        $conn->rollBack();
        $error = "Error al registrar la compra: " . $e->getMessage();
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Nueva Compra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../style/style.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .form-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        .page-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }
        .page-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: #3498db;
            border-radius: 2px;
        }
        .section-title {
            color: #2c3e50;
            font-weight: 500;
            margin-bottom: 1rem;
            font-size: 1.25rem;
        }
        .producto-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }
        .producto-card:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transform: translateY(-1px);
        }
        .form-floating {
            margin-bottom: 1rem;
        }
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #e9ecef;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        .total-section {
            background-color: #2c3e50;
            color: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 2rem;
        }
        .total-label {
            font-size: 1.1rem;
            font-weight: 500;
        }
        .total-amount {
            font-size: 1.5rem;
            font-weight: 600;
        }
        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background-color: #3498db;
            border: none;
        }
        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-1px);
        }
        .btn-outline-secondary {
            border: 2px solid #95a5a6;
            color: #95a5a6;
        }
        .btn-outline-secondary:hover {
            background-color: #95a5a6;
            color: white;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    
<?php include '../navbar.php'; ?>

<div class="container">
    <div class="form-container">
    <form action="compras.php" method="get">
            <button type="submit" class="btn btn-primary mb-3">Volver</button>
        </form>
        <h2 class="page-title text-center">Registrar Nueva Compra</h2>
        
        <form method="POST" class="needs-validation" novalidate>
            <div class="form-floating mb-4">
                <select class="form-select" id="proveedor_id" name="proveedor_id" required>
                    <option value="">Seleccione un proveedor</option>
                    <?php foreach ($proveedores as $proveedor): ?>
                        <option value="<?php echo $proveedor['id']; ?>"><?php echo htmlspecialchars($proveedor['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
                <label for="proveedor_id">Proveedor</label>
                <div class="invalid-feedback">Por favor seleccione un proveedor</div>
            </div>

            <h4 class="section-title">Productos a Comprar</h4>
            <div id="productos">
                <div class="producto-card">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select" name="productos[0][id]" required>
                                    <option value="">Seleccione un producto</option>
                                    <?php foreach ($productos as $producto): ?>
                                        <option value="<?php echo $producto['id']; ?>"><?php echo htmlspecialchars($producto['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <label>Producto</label>
                                <div class="invalid-feedback">Por favor seleccione un producto</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating mb-3">
                                <input type="number" class="form-control" name="productos[0][cantidad]" placeholder="Cantidad" required min="1">
                                <label>Cantidad</label>
                                <div class="invalid-feedback">Ingrese una cantidad válida</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-floating mb-3">
                                <input type="number" step="0.01" class="form-control" name="productos[0][precio_unitario]" placeholder="Precio Unitario" required min="0.01">
                                <label>Precio Unitario</label>
                                <div class="invalid-feedback">Ingrese un precio válido</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mb-4">
                <button type="button" class="btn btn-outline-secondary" id="agregarProducto">
                    <i class="fas fa-plus me-2"></i>Agregar Otro Producto
                </button>
            </div>

            <div class="total-section">
                <div class="row align-items-center">
                    <div class="col">
                        <span class="total-label">Total de la Compra:</span>
                    </div>
                    <div class="col-auto">
                        <span class="total-amount">$<span id="totalDisplay">0.00</span></span>
                        <input type="hidden" id="total" name="total" value="0">
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save me-2"></i>Registrar Compra
                </button>
            </div>
        </form>
    </div>
</div>

<?php include '../footer.php'; ?>
<script src="../../assets/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>

<script>
let productoCount = 1;
// Crear un objeto con los precios de los productos
const precios = <?php echo json_encode(array_reduce($productos, function($carry, $producto) {
    $carry[$producto['id']] = $producto['precio'];
    return $carry;
}, [])); ?>;

function agregarProducto() {
    const productosDiv = document.getElementById('productos');
    const nuevoProducto = document.createElement('div');
    nuevoProducto.className = 'producto-card';
    nuevoProducto.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <div class="form-floating mb-3">
                    <select class="form-select" name="productos[${productoCount}][id]" onchange="actualizarPrecio(this)" required>
                        <option value="">Seleccione un producto</option>
                        <?php foreach ($productos as $producto): ?>
                            <option value="<?php echo $producto['id']; ?>"><?php echo htmlspecialchars($producto['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Producto</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-floating mb-3">
                    <input type="number" class="form-control" name="productos[${productoCount}][cantidad]" placeholder="Cantidad" required min="1">
                    <label>Cantidad</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-floating mb-3">
                    <input type="number" step="0.01" class="form-control" name="productos[${productoCount}][precio_unitario]" placeholder="Precio Unitario" required min="0.01" readonly>
                    <label>Precio Unitario</label>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-outline-danger btn-sm mt-2" onclick="eliminarProducto(this)">
            <i class="fas fa-trash me-2"></i>Eliminar Producto
        </button>
    `;
    productosDiv.appendChild(nuevoProducto);
    productoCount++;
    
    // Agregar listeners para el cálculo del total
    const nuevosInputs = nuevoProducto.querySelectorAll('input[type="number"]');
    nuevosInputs.forEach(input => {
        input.addEventListener('input', calcularTotal);
    });
}

function actualizarPrecio(select) {
    const precio = precios[select.value] || '';
    const precioInput = select.closest('.row').querySelector('input[name$="[precio_unitario]"]');
    precioInput.value = precio;
    calcularTotal();
}

// También necesitamos modificar el primer producto para que use el precio automático
document.addEventListener('DOMContentLoaded', function() {
    const primerSelect = document.querySelector('select[name="productos[0][id]"]');
    primerSelect.addEventListener('change', function() {
        actualizarPrecio(this);
    });
});

function eliminarProducto(button) {
    button.closest('.producto-card').remove();
    calcularTotal();
}

function calcularTotal() {
    let total = 0;
    const productos = document.querySelectorAll('.producto-card');
    
    productos.forEach(producto => {
        const cantidad = parseFloat(producto.querySelector('input[name$="[cantidad]"]').value) || 0;
        const precio = parseFloat(producto.querySelector('input[name$="[precio_unitario]"]').value) || 0;
        total += cantidad * precio;
    });

    document.getElementById('total').value = total.toFixed(2);
    document.getElementById('totalDisplay').textContent = total.toFixed(2);
}

document.getElementById('agregarProducto').addEventListener('click', agregarProducto);

// Validación del formulario
(function() {
    'use strict'
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();

// Inicializar el cálculo del total
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('input[type="number"]');
    inputs.forEach(input => {
        input.addEventListener('input', calcularTotal);
    });
});
</script>

</body>
</html>