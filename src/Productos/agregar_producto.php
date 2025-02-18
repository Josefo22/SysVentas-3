<?php
require_once "../../db.php"; // Conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $categoria_id = $_POST['categoria_id'];

    // Insertar el nuevo producto
    $stmt = $conn->prepare("INSERT INTO productos (nombre, descripcion, precio, stock, categoria_id, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$nombre, $descripcion, $precio, $stock, $categoria_id]);

    header("Location: productos.php");
    exit();
}

// Obtener categorías para el select
$categorias = $conn->query("SELECT id, nombre FROM categorias");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Agregar Producto</title>
    <link rel="stylesheet" href="../../style/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        .page-header {
            background-color: #fff;
            padding: 20px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .page-title {
            color: #2c3e50;
            font-weight: 600;
        }

        .btn {
            border-radius: 5px;
            padding: 8px 16px;
            transition: all 0.3s;
        }

        .btn-success {
            background-color: #2ecc71;
            border: none;
        }

        .btn-success:hover {
            background-color: #27ae60;
            transform: translateY(-2px);
        }
        
        .btn-primary {
            background-color: #3498db;
            border: none;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #95a5a6;
            border: none;
        }

        .btn-secondary:hover {
            background-color: #7f8c8d;
            transform: translateY(-2px);
        }

        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }

        .card-header {
            border-radius: 8px 8px 0 0 !important;
            background-color: #34495e;
            color: white;
            padding: 15px 20px;
        }

        .card-body {
            padding: 25px;
        }

        .form-label {
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 5px;
            padding: 10px 12px;
            border: 1px solid #ddd;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(52, 73, 94, 0.25);
            border-color: #34495e;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-text {
            color: #7f8c8d;
        }

        .input-group-text {
            background-color: #f1f3f5;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <?php include '../navbar.php'; ?>

    <div class="page-header">
        <div class="container">
            <h2 class="text-center page-title">
                <i class="fas fa-box-open me-2"></i>Agregar Nuevo Producto
            </h2>
        </div>
    </div>

    <div class="container mb-5">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Información del Producto</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label class="form-label">
                                <i class="fas fa-tag me-1"></i>Nombre del Producto
                            </label>
                            <input type="text" name="nombre" class="form-control" 
                                   placeholder="Ingrese el nombre del producto" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-align-left me-1"></i>Descripción
                        </label>
                        <textarea name="descripcion" class="form-control" rows="3"
                                  placeholder="Describa las características del producto"></textarea>
                        <div class="form-text">
                            Incluya información relevante como material, modelo, características especiales, etc.
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label class="form-label">
                                <i class="fas fa-dollar-sign me-1"></i>Precio
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" name="precio" class="form-control" 
                                       step="0.01" min="0" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="form-label">
                                <i class="fas fa-cubes me-1"></i>Stock Inicial
                            </label>
                            <input type="number" name="stock" class="form-control" 
                                   min="0" placeholder="Cantidad disponible" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-folder me-1"></i>Categoría
                        </label>
                        <select name="categoria_id" class="form-select" required>
                            <option value="">Seleccione una categoría</option>
                            <?php while ($categoria = $categorias->fetch(PDO::FETCH_ASSOC)): ?>
                                <option value="<?php echo $categoria['id']; ?>">
                                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                
                    <div class="d-flex justify-content-end mt-4 gap-2">
                        <a href="productos.php" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save me-1"></i>Guardar Producto
                        </button>
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