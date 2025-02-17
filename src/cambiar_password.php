<?php
require_once "../db.php"; // Asegúrate de incluir la conexión a la base de datossession_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['usuario'])) {
    header('Location: ../login.php');
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$error = '';
$success = '';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_actual = $_POST['password_actual'] ?? '';
    $password_nuevo = $_POST['password_nuevo'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';
    
    // Validaciones básicas
    if (empty($password_actual) || empty($password_nuevo) || empty($confirmar_password)) {
        $error = 'Todos los campos son obligatorios';
    } elseif (strlen($password_nuevo) < 8) {
        $error = 'La nueva contraseña debe tener al menos 8 caracteres';
    } elseif ($password_nuevo !== $confirmar_password) {
        $error = 'Las contraseñas nuevas no coinciden';
    } else {
        try {
            // Verificar la contraseña actual
            $stmt = $conn->prepare('SELECT password FROM usuarios WHERE id = ?');
            $stmt->execute([$id_usuario]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario && password_verify($password_actual, $usuario['password'])) {
                // Actualizar la contraseña
                $password_hash = password_hash($password_nuevo, PASSWORD_DEFAULT);
                $stmt = $conn->prepare('UPDATE usuarios SET password = ?, fecha_modificacion = NOW() WHERE id = ?');
                
                if ($stmt->execute([$password_hash, $id_usuario])) {
                    $success = 'Contraseña actualizada correctamente';
                    
                    // Registrar el cambio en el log
                    $stmt_log = $conn->prepare('INSERT INTO log_cambios (id_usuario, accion, tabla, fecha) VALUES (?, ?, ?, NOW())');
                    $stmt_log->execute([$id_usuario, 'Cambio de contraseña', 'usuarios']);
                } else {
                    $error = 'Error al actualizar la contraseña';
                }
            } else {
                $error = 'La contraseña actual es incorrecta';
            }
        } catch (PDOException $e) {
            $error = 'Error en la base de datos: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cambiar Contraseña - Sistema de Farmacia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>

<?php include '../templates/navbar.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-key me-2"></i>Cambiar Contraseña</h4>
                </div>
                <div class="card-body">
                    
                    <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="formCambiarPassword">
                        <div class="mb-3">
                            <label for="password_actual" class="form-label">Contraseña Actual</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password_actual" name="password_actual" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_actual">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password_nuevo" class="form-label">Nueva Contraseña</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password_nuevo" name="password_nuevo" 
                                       required minlength="8">
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_nuevo">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                La contraseña debe tener al menos 8 caracteres
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirmar_password" class="form-label">Confirmar Nueva Contraseña</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirmar_password" name="confirmar_password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirmar_password">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="progress" style="height: 5px;">
                                <div id="password-strength" class="progress-bar" role="progressbar" style="width: 0%;" 
                                     aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <small id="password-strength-text" class="form-text">Seguridad de la contraseña</small>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Actualizar Contraseña
                            </button>
                            <a href="../dashboard.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Volver al Inicio
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Función para mostrar/ocultar contraseñas
    const toggleButtons = document.querySelectorAll('.toggle-password');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    });
    
    // Validar que las contraseñas coincidan
    const passwordNuevo = document.getElementById('password_nuevo');
    const confirmarPassword = document.getElementById('confirmar_password');
    const form = document.getElementById('formCambiarPassword');
    
    form.addEventListener('submit', function(event) {
        if (passwordNuevo.value !== confirmarPassword.value) {
            event.preventDefault();
            alert('Las contraseñas nuevas no coinciden');
        }
    });
    
    // Medidor de seguridad de contraseña
    passwordNuevo.addEventListener('input', function() {
        const password = this.value;
        const strengthBar = document.getElementById('password-strength');
        const strengthText = document.getElementById('password-strength-text');
        
        // Calcular fortaleza de la contraseña
        let strength = 0;
        
        // Longitud
        if (password.length >= 8) strength += 25;
        
        // Caracteres especiales
        if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 25;
        
        // Números
        if (/\d/.test(password)) strength += 25;
        
        // Mayúsculas y minúsculas
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 25;
        
        // Actualizar barra de progreso
        strengthBar.style.width = strength + '%';
        strengthBar.setAttribute('aria-valuenow', strength);
        
        // Cambiar color según la fuerza
        if (strength < 25) {
            strengthBar.className = 'progress-bar bg-danger';
            strengthText.textContent = 'Contraseña muy débil';
        } else if (strength < 50) {
            strengthBar.className = 'progress-bar bg-warning';
            strengthText.textContent = 'Contraseña débil';
        } else if (strength < 75) {
            strengthBar.className = 'progress-bar bg-info';
            strengthText.textContent = 'Contraseña moderada';
        } else {
            strengthBar.className = 'progress-bar bg-success';
            strengthText.textContent = 'Contraseña fuerte';
        }
    });
});
</script>

</body>
</html>