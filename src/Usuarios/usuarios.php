<?php
session_start();
require_once "../../db.php"; // Conexión a la base de datos

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Obtener los usuarios de la base de datos
$sql = "SELECT id, nombre, correo, rol FROM usuarios";
$stmt = $conn->query($sql);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="../../style/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --success-color: #2ecc71;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .page-header {
            background-color: var(--secondary-color);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 10px 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            border-color: var(--warning-color);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .table {
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
        }
        
        .table-dark th {
            background-color: var(--secondary-color) !important;
            border-color: var(--secondary-color) !important;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .badge {
            padding: 6px 10px;
            border-radius: 6px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include '../navbar.php'; ?>

    <!-- Header -->
    <div class="page-header">
        <div class="container">
            <h1 class="text-center"><i class="fas fa-users me-2"></i>Gestión de Usuarios</h1>
        </div>
    </div>

    <div class="container">
        <!-- Card principal -->
        <div class="card">
            <div class="card-header py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-table me-2"></i>Lista de Usuarios</h5>
                    <a href="usuario_nuevo.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>Agregar Usuario
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Tabla de usuarios -->
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th><i class="fas fa-id-card me-1"></i> ID</th>
                                <th><i class="fas fa-user me-1"></i> Nombre</th>
                                <th><i class="fas fa-envelope me-1"></i> Email</th>
                                <th><i class="fas fa-user-tag me-1"></i> Rol</th>
                                <th><i class="fas fa-cogs me-1"></i> Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td><?= htmlspecialchars($usuario['id']) ?></td>
                                <td><?= htmlspecialchars($usuario['nombre']) ?></td>
                                <td><?= htmlspecialchars($usuario['correo']) ?></td>
                                <td>
                                    <?php
                                    $badgeClass = '';
                                    switch (strtolower($usuario['rol'])) {
                                        case 'admin':
                                        case 'administrador':
                                            $badgeClass = 'bg-danger';
                                            break;
                                        case 'editor':
                                            $badgeClass = 'bg-warning text-dark';
                                            break;
                                        case 'usuario':
                                            $badgeClass = 'bg-info text-dark';
                                            break;
                                        default:
                                            $badgeClass = 'bg-secondary';
                                    }
                                    ?>
                                    <span class="badge <?= $badgeClass ?>">
                                        <?= htmlspecialchars($usuario['rol']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="usuario_editar.php?id=<?= $usuario['id'] ?>" 
                                           class="btn btn-warning btn-sm" 
                                           data-bs-toggle="tooltip" 
                                           title="Editar usuario">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="usuario_eliminar.php?id=<?= $usuario['id'] ?>" 
                                           class="btn btn-danger btn-sm"
                                           data-bs-toggle="tooltip"
                                           title="Eliminar usuario"
                                           onclick="return confirm('¿Estás seguro que deseas eliminar este usuario?');">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginación (si se necesita) -->
                <nav aria-label="Navegación de páginas" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <li class="page-item disabled">
                            <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Anterior</a>
                        </li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item">
                            <a class="page-link" href="#">Siguiente</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include '../footer.php'; ?>
    
    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script src="../../assets/bootstrap.bundle.min.js"></script>
    <script src="../../assets/bootstrap.bundle.min.js"></script>
    <script>
    // Inicializar tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
    </script>
</body>
</html>