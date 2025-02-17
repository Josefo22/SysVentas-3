<?php
session_start();
require_once "../../db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);
    $rol = $_POST['rol'];

    $sql = "INSERT INTO usuarios (nombre, email, contrasena, rol) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$nombre, $email, $contrasena, $rol]);

    header("Location: usuarios.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nuevo Usuario</title>
    <link rel="stylesheet" href="../../style/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .form-title {
            color: #333;
            margin-bottom: 25px;
        }

        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
        }
    </style>
</head>
<body class="bg-light">

<?php include '../navbar.php'; ?>

<div class="container mt-4">
    <div class="form-container">
        <h2 class="text-center form-title">Agregar Nuevo Usuario</h2>
        <form method="POST" id="userForm">
            <div class="mb-3">
                <label class="form-label">Nombre:</label>
                <input type="text" 
                       name="nombre" 
                       class="form-control" 
                       required 
                       placeholder="Ingrese el nombre completo">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Email:</label>
                <input type="email" 
                       name="email" 
                       class="form-control" 
                       required 
                       placeholder="ejemplo@dominio.com">
            </div>
            
            <div class="mb-3">
                <label class="form-label">Contraseña:</label>
                <input type="password" 
                       name="contrasena" 
                       id="contrasena" 
                       class="form-control" 
                       required 
                       placeholder="Ingrese la contraseña">
                <small class="text-muted">Mínimo 8 caracteres</small>
            </div>
            
            <div class="mb-4">
                <label class="form-label">Rol:</label>
                <select name="rol" class="form-control" required>
                    <option value="">Seleccione un rol</option>
                    <option value="admin">Administrador</option>
                    <option value="empleado">Empleado</option>
                </select>
            </div>
            
            <div class="d-flex justify-content-end gap-2">
                <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success">Guardar</button>
            </div>
        </form>
    </div>
</div>
<br>
<?php include '../footer.php'; ?>

<script src="../../assets/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('userForm').addEventListener('submit', function(e) {
    const password = document.getElementById('contrasena').value;
    
    if (password.length < 8) {
        e.preventDefault();
        alert('La contraseña debe tener al menos 8 caracteres');
    }
});
</script>
</body>
</html>