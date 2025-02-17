<?php
session_start();
if (isset($_SESSION['usuario'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Sistema de Gestión de Farmacia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style/style.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --hover-color: #0b5ed7;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-card {
            width: 380px;
            border-radius: 10px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            overflow: hidden;
            background-color: white;
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary-color), #6610f2);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .login-logo {
            width: 70px;
            height: 70px;
            background-color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }
        
        .login-logo i {
            font-size: 35px;
            color: var(--primary-color);
        }
        
        .login-body {
            padding: 30px;
        }
        
        .form-floating {
            margin-bottom: 20px;
        }
        
        .password-field {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 10;
        }
        
        .btn-login {
            padding: 10px;
            font-weight: 500;
            letter-spacing: 0.5px;
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            background-color: var(--hover-color);
            border-color: var(--hover-color);
            box-shadow: 0 0.125rem 0.25rem rgba(13, 110, 253, 0.5);
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }
        
        .forgot-password a {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }
        
        .alert {
            border-radius: 5px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">
                <i class="fas fa-prescription-bottle-alt"></i>
            </div>
            <h4>Sistema de Gestión</h4>
            <p class="mb-0">Farmacia</p>
        </div>
        
        <div class="login-body">
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger text-center mb-4">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>
            
            <form action="procesar_login.php" method="POST">
                <div class="form-floating">
                    <input type="email" class="form-control" id="floatingEmail" name="correo" placeholder="correo@ejemplo.com" required>
                    <label for="floatingEmail"><i class="fas fa-envelope me-2"></i>Correo Electrónico</label>
                </div>
                
                <div class="form-floating password-field">
                    <input type="password" class="form-control" id="floatingPassword" name="password" placeholder="Contraseña" required>
                    <label for="floatingPassword"><i class="fas fa-lock me-2"></i>Contraseña</label>
                    <span class="toggle-password" onclick="togglePasswordVisibility()">
                    </span>
                </div>
                
                <button type="submit" class="btn btn-login btn-primary w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>Ingresar al Sistema
                </button>
                
                <div class="forgot-password">
                    <a href="recuperar_password.php">¿Olvidaste tu contraseña?</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'src/footer.php'; ?>

<script>
function togglePasswordVisibility() {
    const passwordField = document.getElementById('floatingPassword');
    const passwordIcon = document.getElementById('password-icon');
    
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        passwordIcon.classList.remove('fa-eye');
        passwordIcon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        passwordIcon.classList.remove('fa-eye-slash');
        passwordIcon.classList.add('fa-eye');
    }
}
</script>

</body>
</html>