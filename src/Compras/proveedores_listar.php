<?php
require_once "../../db.php";

// Obtener todos los proveedores
$stmt = $conn->prepare("SELECT p.*, 
                        (SELECT COUNT(*) FROM compras WHERE proveedor_id = p.id) as total_compras,
                        (SELECT SUM(total) FROM compras WHERE proveedor_id = p.id) as total_monto
                       FROM proveedores p
                       ORDER BY nombre ASC");
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Mantener los mismos estilos del archivo original -->
</head>
<body>
    <?php include '../navbar.php'; ?>

    <div class="page-header">
        <div class="container">
            <h2 class="text-center page-title">
                <br>
                <i class="fas fa-truck me-2"></i>Gestión de Proveedores
            </h2>
            <div class="d-flex justify-content-center action-buttons">
                <a href="proveedores_nuevo.php" class="btn btn-success">
                    <i class="fas fa-plus-circle me-2"></i>Nuevo Proveedor
                </a>
            </div>
            <form action="compras.php" method="get">
            <button type="submit" class="btn btn-primary mb-3">Volver</button>
        </form>
        </div>
    </div>
<br>
    <div class="container mb-5">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-list me-2"></i>Listado de Proveedores</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Contacto</th>
                                <th>Email</th>
                                <th>Total Compras</th>
                                <th>Monto Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($proveedores as $proveedor): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-building text-secondary me-2"></i>
                                            <?= htmlspecialchars($proveedor['nombre']) ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($proveedor['telefono']) ?></td>
                                    <td><?= htmlspecialchars($proveedor['correo']) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">
                                            <?= $proveedor['total_compras'] ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-success">
                                            $<?= number_format($proveedor['total_monto'], 2) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="proveedores_editar.php?id=<?= $proveedor['id'] ?>" 
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="proveedores_historial.php?id=<?= $proveedor['id'] ?>" 
                                               class="btn btn-info btn-sm">
                                                <i class="fas fa-history"></i>
                                            </a>
                                        </div>
                                    </td>
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
</body>
</html>