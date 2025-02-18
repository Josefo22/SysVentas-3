<?php
// Iniciar sesión solo si no ha sido iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Sistema de Farmacia</title>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <!-- Logo y botón de hamburguesa -->
        <a class="navbar-brand" href="/Farmacia/src/dashboard.php">
            <i class="bi bi-capsule me-2"></i>Farmacia
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" 
                data-bs-target="#navbarContent" aria-controls="navbarContent" 
                aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Contenido del navbar -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <!-- Inicio -->
                <li class="nav-item">
                    <a class="nav-link" href="/Farmacia/src/dashboard.php">
                        <i class="bi bi-house-door me-1"></i>Inicio
                    </a>
                </li>
                
                <!-- Módulos -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="modulosDropdown" 
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-grid-3x3-gap me-1"></i>Módulos
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="modulosDropdown">
                        <li><a class="dropdown-item" href="/Farmacia/src/usuarios/usuarios.php">
                            <i class="bi bi-people me-2"></i>Usuarios</a></li>
                        <li><a class="dropdown-item" href="/Farmacia/src/productos/productos.php">
                            <i class="bi bi-box-seam me-2"></i>Productos</a></li>
                        <li><a class="dropdown-item" href="/Farmacia/src/clientes/clientes.php">
                            <i class="bi bi-person-vcard me-2"></i>Clientes</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/Farmacia/src/ventas/ventas.php">
                            <i class="bi bi-cart-check me-2"></i>Ventas</a></li>
                        <li><a class="dropdown-item" href="/Farmacia/src/compras/compras.php">
                            <i class="bi bi-cart-plus me-2"></i>Compras</a></li>
                        <li><a class="dropdown-item" href="/Farmacia/src\Prooveedores\proveedores.php">
                            <i class="bi bi-truck me-2"></i>Proveedores</a></li>
                    </ul>
                </li>

                <!-- Reportes -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="reportesDropdown" 
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-file-earmark-text me-1"></i>Reportes
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="reportesDropdown">
                        <li><a class="dropdown-item" href="/Farmacia/src/reportes/reporte_productos.php">
                            <i class="bi bi-box-seam me-2"></i>Productos</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/Farmacia/src/reportes/reporte_ventas.php">
                            <i class="bi bi-cart-check me-2"></i>Ventas</a></li>
                        <li><a class="dropdown-item" href="/Farmacia/src/reportes/reporte_compras.php">
                            <i class="bi bi-cart-plus me-2"></i>Compras</a></li>
                    </ul>
                </li>
                
                <!-- Cierre de caja -->
                <li class="nav-item">
                    <a class="nav-link" href="/Farmacia/src/cierre_caja.php">
                        <i class="bi bi-cash-register me-1"></i>Cierre de Caja
                    </a>
                </li>
            </ul>
            
            <!-- Perfil y logout -->
            <div class="d-flex align-items-center">
                <div class="dropdown">
                    <a class="nav-link dropdown-toggle text-white me-3" href="#" id="userDropdown" 
                       role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-1"></i>
                        <?= htmlspecialchars($_SESSION['usuario'] ?? 'Invitado') ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="/Farmacia/src/mi_perfil.php">
                            <i class="bi bi-person me-2"></i>Mi Perfil</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/Farmacia/logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>Cerrar sesión</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>

<!-- Incluir Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<!-- Bootstrap JS bundle con Popper incluido -->
<script src="../../assets/bootstrap.bundle.min.js"></script>

</body>
</html>