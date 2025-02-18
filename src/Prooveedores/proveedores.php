<?php
require_once "../../db.php";

// Obtener todos los proveedores
$stmt = $conn->prepare("SELECT id, nombre, telefono, correo, direccion, created_at FROM proveedores ORDER BY nombre ASC");
$stmt->execute();
$proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contar el número total de proveedores
$stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM proveedores");
$stmt_count->execute();
$total_proveedores = $stmt_count->fetch(PDO::FETCH_ASSOC)['total'];

// Contar proveedores añadidos este mes
$stmt_month = $conn->prepare("SELECT COUNT(*) as total FROM proveedores WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
$stmt_month->execute();
$proveedores_mes = $stmt_month->fetch(PDO::FETCH_ASSOC)['total'];

// Contar proveedores activos (con compras en los últimos 3 meses)
$stmt_active = $conn->prepare("SELECT COUNT(DISTINCT p.id) as total 
                              FROM proveedores p 
                              JOIN compras c ON p.id = c.proveedor_id 
                              WHERE c.fecha >= DATE_SUB(CURRENT_DATE(), INTERVAL 3 MONTH)");
$stmt_active->execute();
$proveedores_activos = $stmt_active->fetch(PDO::FETCH_ASSOC)['total'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Proveedores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../style/style.css">
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
            margin-bottom: 20px;
        }

        .action-buttons {
            gap: 10px;
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
        
        .btn-info {
            background-color: #00bcd4;
            border: none;
            color: white;
        }
        
        .btn-info:hover {
            background-color: #00a5bb;
            transform: translateY(-2px);
            color: white;
        }
        
        .btn-warning {
            background-color: #f39c12;
            border: none;
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #e67e22;
            transform: translateY(-2px);
            color: white;
        }

        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
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
        
        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .table thead th {
            background-color: #34495e;
            color: white;
            padding: 12px;
        }

        .table tbody td {
            padding: 12px;
            vertical-align: middle;
        }
        
        .contact-badge {
            padding: 6px 12px;
            border-radius: 15px;
            font-weight: 500;
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .date-badge {
            padding: 6px 12px;
            border-radius: 15px;
            font-weight: 500;
            background-color: #e3f2fd;
            color: #1976d2;
        }
        
        .action-column {
            display: flex;
            gap: 5px;
        }
        
        .search-container {
            margin-bottom: 20px;
        }
        
        .input-group-text {
            background-color: #34495e;
            color: white;
            border: none;
        }
    </style>
</head>
<body>

<?php include '../navbar.php'; ?>

<div class="page-header">
    <div class="container">
        <h2 class="text-center page-title">
            <i class="fas fa-address-book me-2"></i>Gestión de Proveedores
        </h2>
        <div class="d-flex justify-content-center action-buttons">
            <a href="proveedor_nuevo.php" class="btn btn-success">
                <i class="fas fa-plus-circle me-2"></i>Nuevo Proveedor
            </a>
        </div>
    </div>
</div>

<div class="container mb-5">
    <!-- Buscador -->
    <div class="card mb-4">
        <div class="card-body search-container">
            <form action="" method="GET" class="d-flex align-items-center">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="buscar" class="form-control" placeholder="Buscar por nombre, teléfono o correo..." value="<?= isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : '' ?>">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <?php if(isset($_GET['buscar'])): ?>
                        <a href="proveedores_listar.php" class="btn btn-outline-secondary">Limpiar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Card para Listado de Proveedores -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="mb-0"><i class="fas fa-list me-2"></i>Listado de Proveedores</h3>
            <span class="badge bg-primary"><?= count($proveedores) ?> proveedores</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag me-2"></i>ID</th>
                            <th><i class="fas fa-building me-2"></i>Nombre</th>
                            <th><i class="fas fa-phone me-2"></i>Teléfono</th>
                            <th><i class="fas fa-envelope me-2"></i>Correo</th>
                            <th><i class="fas fa-calendar-alt me-2"></i>Fecha Registro</th>
                            <th><i class="fas fa-cogs me-2"></i>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($proveedores) > 0): ?>
                            <?php foreach ($proveedores as $proveedor): ?>
                                <tr>
                                    <td><?= $proveedor['id'] ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-building text-secondary me-2"></i>
                                            <strong><?= htmlspecialchars($proveedor['nombre']) ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="contact-badge">
                                            <i class="fas fa-phone me-1"></i>
                                            <?= htmlspecialchars($proveedor['telefono']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="mailto:<?= htmlspecialchars($proveedor['correo']) ?>" class="text-decoration-none">
                                            <i class="fas fa-envelope text-primary me-1"></i>
                                            <?= htmlspecialchars($proveedor['correo']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="date-badge">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?= date('d/m/Y', strtotime($proveedor['created_at'])) ?>
                                        </span>
                                    </td>
                                    <td class="action-column">
                                        <a href="proveedor_detalle.php?id=<?= $proveedor['id'] ?>" 
                                           class="btn btn-info btn-sm">
                                            <i class="fas fa-eye me-1"></i>Ver
                                        </a>
                                        <a href="proveedor_editar.php?id=<?= $proveedor['id'] ?>" 
                                           class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit me-1"></i>Editar
                                        </a>
                                        <a href="proveedor_eliminar.php?id=<?= $proveedor['id'] ?>" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('¿Seguro que deseas eliminar este proveedor?');">
                                            <i class="fas fa-trash me-1"></i>Eliminar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-inbox fa-2x mb-3 text-muted"></i>
                                    <p class="mb-0">No hay proveedores registrados aún</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Cards de Información Adicional -->
    <div class="row">
        <!-- Card de Estadísticas Rápidas -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Resumen de Proveedores</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-center">
                        <div class="text-center px-4">
                            <i class="fas fa-users fa-2x mb-2 text-primary"></i>
                            <h5>Total</h5>
                            <h4 class="text-primary"><?= $total_proveedores ?></h4>
                        </div>
                        <div class="text-center px-4">
                            <i class="fas fa-user-plus fa-2x mb-2 text-success"></i>
                            <h5>Este mes</h5>
                            <h4 class="text-success"><?= $proveedores_mes ?></h4>
                        </div>
                        <div class="text-center px-4">
                            <i class="fas fa-user-check fa-2x mb-2 text-info"></i>
                            <h5>Activos</h5>
                            <h4 class="text-info"><?= $proveedores_activos ?></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Card de Acciones Rápidas -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h4 class="mb-0"><i class="fas fa-bolt me-2"></i>Acciones Rápidas</h4>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        <a href="proveedor_importar.php" class="btn btn-outline-primary">
                            <i class="fas fa-file-import me-2"></i>Importar Proveedores
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
<script src="../../assets/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Script para filtrar la tabla de proveedores
$(document).ready(function(){
    $("#filtroProveedores").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#tablaProveedores tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>

</body>
</html>