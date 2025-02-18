<?php
require_once "../../db.php";

// Obtener datos del proveedor
if (!isset($_GET['id'])) {
    header('Location: proveedores.php');
    exit;
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM proveedores WHERE id = ?");
$stmt->execute([$id]);
$proveedor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$proveedor) {
    header('Location: proveedores.php');
    exit;
}

// Procesar formulario de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $conn->prepare("UPDATE proveedores SET 
            nombre = ?, 
            telefono = ?, 
            correo = ?,
            direccion = ?,
            notas = ?
            WHERE id = ?");
            
        $stmt->execute([
            $_POST['nombre'],
            $_POST['telefono'],
            $_POST['correo'],
            $_POST['direccion'],
            $_POST['notas'],
            $id
        ]);

        header('Location: proveedores.php?success=1');
        exit;
    } catch (PDOException $e) {
        $error = "Error al actualizar el proveedor: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Proveedor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../style/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include '../navbar.php'; ?>

    <div class="page-header">
        <div class="container">
            <h2 class="text-center page-title">
                <i class="fas fa-edit me-2"></i>Editar Proveedor
            </h2>
        </div>
    </div>

    <div class="container mb-5">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0">
                    <i class="fas fa-user-edit me-2"></i>Editar <?= htmlspecialchars($proveedor['nombre']) ?>
                </h3>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" class="form-control" 
                                   value="<?= htmlspecialchars($proveedor['nombre']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" name="telefono" class="form-control" 
                                   value="<?= htmlspecialchars($proveedor['telefono']) ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" name="correo" class="form-control" 
                                   value="<?= htmlspecialchars($proveedor['correo']) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Dirección</label>
                            <input type="text" name="direccion" class="form-control" 
                                   value="<?= htmlspecialchars($proveedor['direccion']) ?>">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="compras.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Volver
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>
    <script src="../../assets/bootstrap.bundle.min.js"></script>
</body>
</html>