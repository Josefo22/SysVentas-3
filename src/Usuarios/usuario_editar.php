<?php
session_start();
require_once "../../db.php";

$id = $_GET['id'];
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $rol = $_POST['rol'];

    $sql = "UPDATE usuarios SET nombre = ?, email = ?, rol = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$nombre, $email, $rol, $id]);

    header("Location: usuarios.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="../../style/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Agregamos Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #f5f5f5;
            --accent-color: #2ecc71;
            --danger-color: #e74c3c;
        }

        body {
            background-color: #f0f2f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .custom-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            margin-top: 2rem;
            transition: transform 0.3s ease;
        }

        .custom-card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            background: var(--primary-color);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 1.5rem;
        }

        .form-control {
            border-radius: 8px;
            border: 2px solid #e1e1e1;
            padding: 10px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.25);
        }

        .custom-select {
            border-radius: 8px;
            border: 2px solid #e1e1e1;
            padding: 10px 15px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-success {
            background-color: var(--accent-color);
            border: none;
        }

        .btn-success:hover {
            background-color: #27ae60;
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

        .form-label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .input-group-text {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px 0 0 8px;
        }

        .alert {
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .icon-container {
            margin-right: 8px;
        }
    </style>
</head>
<body>

<?php include '../navbar.php'; ?>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="custom-card">
                <div class="card-header">
                    <h2 class="text-center mb-0">
                        <i class="fas fa-user-edit me-2"></i>Editar Usuario
                    </h2>
                </div>
                <div class="card-body p-4">
                    <form method="POST" id="editUserForm">
                        <div class="mb-4">
                            <label for="nombre" class="form-label">
                                <i class="fas fa-user icon-container"></i>Nombre
                            </label>
                            <input type="text" 
                                   name="nombre" 
                                   id="nombre" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($usuario['nombre']) ?>" 
                                   required>
                        </div>

                        <div class="mb-4">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope icon-container"></i>Email
                            </label>
                            <input type="email" 
                                   name="email" 
                                   id="email" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($usuario['correo']) ?>" 
                                   required>
                        </div>

                        <div class="mb-4">
                            <label for="rol" class="form-label">
                                <i class="fas fa-user-tag icon-container"></i>Rol
                            </label>
                            <select name="rol" id="rol" class="form-control custom-select">
                                <option value="admin" <?= $usuario['rol'] == 'admin' ? 'selected' : '' ?>>
                                    Administrador
                                </option>
                                <option value="empleado" <?= $usuario['rol'] == 'empleado' ? 'selected' : '' ?>>
                                    Empleado
                                </option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-end gap-3">
                            <a href="usuarios.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>

<script src="../../assets/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editUserForm');
    
    form.addEventListener('submit', function(e) {
        const nombre = document.getElementById('nombre').value.trim();
        const email = document.getElementById('email').value.trim();
        
        if (nombre === '') {
            e.preventDefault();
            alert('Por favor, ingrese un nombre válido');
            return;
        }
        
        if (email === '' || !isValidEmail(email)) {
            e.preventDefault();
            alert('Por favor, ingrese un email válido');
            return;
        }
    });
    
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // Efecto hover en inputs
    const inputs = document.querySelectorAll('.form-control, .custom-select');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.style.transform = 'translateY(-2px)';
        });
        
        input.addEventListener('blur', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});
</script>
</body>
</html>