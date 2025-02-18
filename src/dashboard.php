<?php
session_start();
require_once "../db.php"; // Asegúrate de incluir la conexión a la base de datos

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Verificar si la caja ya ha sido abierta hoy
$sql = "SELECT COUNT(*) as abierta FROM caja WHERE DATE(fecha_apertura) = CURDATE() AND estado = 'abierta'";
try {
    $stmt = $conn->query($sql);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $cajaAbierta = $result ? (int)$result['abierta'] : 0;
} catch (PDOException $e) {
    // Si hay un error en la consulta, asumir que la caja no está abierta
    $cajaAbierta = 0;
}

// Obtener información del usuario actual
$nombreCompleto = isset($_SESSION['usuario']['nombre']) ? htmlspecialchars($_SESSION['usuario']['nombre']) : 'Administrador';
$rolUsuario = isset($_SESSION['usuario']['rol']) ? htmlspecialchars($_SESSION['usuario']['rol']) : 'Usuario';
$idUsuario = isset($_SESSION['usuario']['id']) ? (int)$_SESSION['usuario']['id'] : null;
if ($idUsuario) {
    try {
        $sqlUsuario = "SELECT nombre, apellido FROM usuarios WHERE id = :id";
        $stmtUsuario = $conn->prepare($sqlUsuario);
        $stmtUsuario->bindParam(':id', $idUsuario);
        $stmtUsuario->execute();
        $usuario = $stmtUsuario->fetch(PDO::FETCH_ASSOC);
        if ($usuario) {
            $nombreCompleto = $usuario['nombre'] . ' ' . $usuario['apellido'];
        }
    } catch (PDOException $e) {
        // Si hay un error en la consulta, mantener el valor por defecto
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard - Sistema de Gestión de Farmacia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../style/style.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6610f2;
            --success-color: #198754;
            --info-color: #0dcaf0;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        body {
            background-color: #f5f7fa;
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        .dashboard-title {
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .dashboard-subtitle {
            opacity: 0.8;
            font-size: 1rem;
        }
        
        .dashboard-card {
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
            border: none;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .card-header-custom {
            padding: 15px;
            text-align: center;
            color: white;
        }
        
        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .card-title {
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .card-text {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }
        
        .btn-dashboard {
            border-radius: 50px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s;
            width: 100%;
        }
        
        .btn-dashboard:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .modal-caja {
            border-radius: 15px;
        }
        
        .modal-header-caja {
            background: linear-gradient(135deg, var(--danger-color), #e35d6a);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .nav-user-info {
            display: flex;
            align-items: center;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
        }
        
        .user-name {
            font-weight: 500;
            margin-bottom: 0;
        }
        
        .user-role {
            font-size: 0.8rem;
            opacity: 0.8;
        }
        
        /* Colores para cada sección */
        .bg-usuarios { background-color: var(--primary-color); }
        .bg-productos { background-color: var(--success-color); }
        .bg-clientes { background-color: var(--info-color); }
        .bg-ventas { background-color: var(--warning-color); }
        .bg-compras { background-color: var(--secondary-color); }
        .bg-proveedores { background-color: var(--dark-color); }
    </style>
</head>
<body>

<!-- Navbar -->
<?php include 'navbar.php'; ?>

<!-- Modal para la apertura de caja -->
<?php if (!$cajaAbierta): ?>
<div class="modal fade" id="cajaCerradaModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="cajaCerradaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-caja shadow">
            <div class="modal-header modal-header-caja border-0">
                <h5 class="modal-title" id="cajaCerradaModalLabel">
                    <i class="fas fa-cash-register me-2"></i>Apertura de Caja
                </h5>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-4">
                    <i class="fas fa-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                </div>
                <h4 class="mb-3">¡Atención!</h4>
                <p class="mb-4">Para comenzar las operaciones del día, es necesario realizar la apertura de caja.</p>
                <p class="text-muted mb-4"><small>Sin este paso, no podrás registrar ventas ni otras transacciones financieras.</small></p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <a href="cierre_caja.php" class="btn btn-primary btn-dashboard px-4">
                    <i class="fas fa-lock-open me-2"></i>Abrir Caja
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Header del Dashboard -->
<div class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2 class="dashboard-title mb-1">Panel de Control</h2>
                <p class="dashboard-subtitle mb-0">Sistema de Gestión de Farmacia</p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="nav-user-info d-flex align-items-center">
                    <div class="user-avatar me-2">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <p class="user-name mb-0 fw-bold"><?= $nombreCompleto ?></p>
                        <p class="user-role text-muted mb-0"><?= $rolUsuario ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contenido principal -->
<div class="container mb-5">
    <div class="row g-4">

        <!-- Tarjeta de Usuarios -->
        <div class="col-md-4 col-sm-6">
            <div class="card dashboard-card shadow-sm h-100">
                <div class="card-header-custom bg-usuarios">
                    <i class="fas fa-users card-icon"></i>
                </div>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">Usuarios</h5>
                    <p class="card-text">Gestión completa de usuarios, roles y permisos del sistema.</p>
                    <a href="/Farmacia/src/usuarios/usuarios.php" class="btn btn-primary btn-dashboard mt-auto">
                        <i class="fas fa-cogs me-2"></i>Administrar
                    </a>
                </div>
            </div>
        </div>

        <!-- Tarjeta de Productos -->
        <div class="col-md-4 col-sm-6">
            <div class="card dashboard-card shadow-sm h-100">
                <div class="card-header-custom bg-productos">
                    <i class="fas fa-prescription-bottle-alt card-icon"></i>
                </div>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">Productos</h5>
                    <p class="card-text">Control de inventario, stock, medicamentos y artículos disponibles.</p>
                    <a href="/Farmacia/src/productos/productos.php" class="btn btn-success btn-dashboard mt-auto">
                        <i class="fas fa-pills me-2"></i>Ver Productos
                    </a>
                </div>
            </div>
        </div>

        <!-- Tarjeta de Clientes -->
        <div class="col-md-4 col-sm-6">
            <div class="card dashboard-card shadow-sm h-100">
                <div class="card-header-custom bg-clientes">
                    <i class="fas fa-user-friends card-icon"></i>
                </div>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">Clientes</h5>
                    <p class="card-text">Administración de la cartera de clientes, historial y seguimiento.</p>
                    <a href="\Farmacia\src\Clientes\clientes.php" class="btn btn-info btn-dashboard mt-auto text-white">
                        <i class="fas fa-address-card me-2"></i>Ver Clientes
                    </a>
                </div>
            </div>
        </div>

        <!-- Tarjeta de Ventas -->
        <div class="col-md-4 col-sm-6">
            <div class="card dashboard-card shadow-sm h-100">
                <div class="card-header-custom bg-ventas">
                    <i class="fas fa-cash-register card-icon"></i>
                </div>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">Ventas</h5>
                    <p class="card-text">Gestión de transacciones, facturas y registro de ventas diarias.</p>
                    <a href="\Farmacia\src\Ventas\ventas.php" class="btn btn-warning btn-dashboard mt-auto text-dark">
                        <i class="fas fa-shopping-cart me-2"></i>Ver Ventas
                    </a>
                </div>
            </div>
        </div>

        <!-- Tarjeta de Compras -->
        <div class="col-md-4 col-sm-6">
            <div class="card dashboard-card shadow-sm h-100">
                <div class="card-header-custom bg-compras">
                    <i class="fas fa-truck-loading card-icon"></i>
                </div>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">Compras</h5>
                    <p class="card-text">Control de adquisiciones, órdenes de compra y recepción de mercancía.</p>
                    <a href="\Farmacia\src\Compras\compras.php" class="btn btn-secondary btn-dashboard mt-auto">
                        <i class="fas fa-shopping-basket me-2"></i>Ver Compras
                    </a>
                </div>
            </div>
        </div>

        <!-- Tarjeta de Proveedores -->
        <div class="col-md-4 col-sm-6">
            <div class="card dashboard-card shadow-sm h-100">
                <div class="card-header-custom bg-proveedores">
                    <i class="fas fa-industry card-icon"></i>
                </div>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">Proveedores</h5>
                    <p class="card-text">Administración de proveedores, contactos y catálogos de productos.</p>
                    <a href="\Farmacia\src\Prooveedores\proveedores.php" class="btn btn-dark btn-dashboard mt-auto">
                        <i class="fas fa-handshake me-2"></i>Ver Proveedores
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Footer -->
<?php include 'footer.php'; ?>
<script src="../../assets/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
<?php if (!$cajaAbierta): ?>
document.addEventListener('DOMContentLoaded', function() {
    var cajaCerradaModal = new bootstrap.Modal(document.getElementById('cajaCerradaModal'));
    cajaCerradaModal.show();
});
<?php endif; ?>
</script>
</body>
</html>