<?php
require_once "../../db.php";

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo'])) {
    $archivo = $_FILES['archivo'];
    
    if ($archivo['type'] === 'text/csv') {
        try {
            $handle = fopen($archivo['tmp_name'], 'r');
            $conn->beginTransaction();
            $proveedores_importados = 0;
            
            // Saltar la primera línea (encabezados)
            fgetcsv($handle);
            
            while (($data = fgetcsv($handle)) !== FALSE) {
                $stmt = $conn->prepare("INSERT INTO proveedores (nombre, telefono, correo, direccion) 
                                      VALUES (?, ?, ?, ?)");
                $stmt->execute([$data[0], $data[1], $data[2], $data[3]]);
                $proveedores_importados++;
            }
            
            fclose($handle);
            $conn->commit();
            
            $mensaje = "Se importaron $proveedores_importados proveedores exitosamente.";
            $tipo_mensaje = 'success';
        } catch (Exception $e) {
            $conn->rollBack();
            $mensaje = "Error al importar: " . $e->getMessage();
            $tipo_mensaje = 'danger';
        }
    } else {
        $mensaje = "Por favor, seleccione un archivo CSV válido.";
        $tipo_mensaje = 'warning';
    }
}

// Obtener los proveedores existentes
$stmt = $conn->query("SELECT * FROM proveedores ORDER BY created_at DESC");
$proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Proveedores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../navbar.php'; ?>

    <div class="page-header">
        <div class="container">
            <br>
            <h2 class="text-center page-title">
                <i class="fas fa-file-import me-2"></i>Importar Proveedores
            </h2>
            <form action=".\proveedores.php">
            <button type="submit" class="btn btn-primary mb-3">Volver</button>
        </form>
        </div>
    </div>

    <div class="container mb-5">
        <?php if ($mensaje): ?>
            <div class="alert alert-<?= $tipo_mensaje ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($mensaje) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-upload me-2"></i>Subir Archivo CSV</h3>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label class="form-label">Seleccionar archivo CSV</label>
                        <input type="file" class="form-control" name="archivo" accept=".csv" required>
                        <div class="form-text">
                            El archivo debe ser CSV con las columnas: nombre, teléfono, correo, dirección
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="proveedores.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-2"></i>Importar Proveedores
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-list me-2"></i>Lista de Proveedores</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Correo</th>
                                <th>Dirección</th>
                                <th>Fecha Creación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($proveedores as $proveedor): ?>
                                <tr>
                                    <td><?= htmlspecialchars($proveedor['id']) ?></td>
                                    <td><?= htmlspecialchars($proveedor['nombre']) ?></td>
                                    <td><?= htmlspecialchars($proveedor['telefono']) ?></td>
                                    <td><?= htmlspecialchars($proveedor['correo']) ?></td>
                                    <td><?= htmlspecialchars($proveedor['direccion']) ?></td>
                                    <td><?= htmlspecialchars($proveedor['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>
    <script src="../../assets/bootstrap.bundle.min.js"></script>
    <script src="../../assets/bootstrap.bundle.min.js"></script>
</body>
</html>
