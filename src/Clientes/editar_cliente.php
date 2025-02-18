<?php
require_once "../../db.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmt->execute([$id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cliente) {
        header("Location: clientes.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];
    $correo = $_POST['correo'];

    $stmt = $conn->prepare("UPDATE clientes SET nombre = ?, telefono = ?, direccion = ?, correo = ? WHERE id = ?");
    $stmt->execute([$nombre, $telefono, $direccion, $correo, $id]);

    header("Location: clientes.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Cliente</title>
    <link rel="stylesheet" href="../../style/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .form-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            margin-top: 2rem;
            margin-bottom: 2rem;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        .page-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
        }
        .page-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 3px;
            background: #3498db;
            border-radius: 2px;
        }
        .client-id {
            text-align: center;
            color: #7f8c8d;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }
        .form-section {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .form-section h5 {
            color: #2c3e50;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        .form-floating {
            margin-bottom: 1.5rem;
        }
        .form-control {
            border-radius: 8px;
            border: 1px solid #e9ecef;
            padding: 0.75rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        .buttons-container {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        .btn {
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary {
            background-color: #3498db;
            border: none;
        }
        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background-color: #95a5a6;
            border: none;
        }
        .btn-secondary:hover {
            background-color: #7f8c8d;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
<?php include '../navbar.php'; ?>

<div class="container">
    <div class="form-container">
        <h2 class="page-title text-center">Editar Cliente</h2>
        <div class="client-id">ID del Cliente: <?= htmlspecialchars($cliente['id']) ?></div>
        
        <form method="post" class="needs-validation" novalidate>
            <div class="form-section">
                <h5>Información Personal</h5>
                <div class="form-floating">
                    <input type="text" 
                           class="form-control" 
                           id="nombre" 
                           name="nombre" 
                           placeholder="Nombre completo"
                           value="<?= htmlspecialchars($cliente['nombre']) ?>"
                           required
                           pattern="[A-Za-zÀ-ÿ\s]{3,50}">
                    <label for="nombre">Nombre completo</label>
                    <div class="invalid-feedback">
                        Por favor ingrese un nombre válido (mínimo 3 caracteres)
                    </div>
                </div>

                <div class="form-floating">
                    <input type="tel" 
                           class="form-control" 
                           id="telefono" 
                           name="telefono" 
                           placeholder="Teléfono"
                           value="<?= htmlspecialchars($cliente['telefono']) ?>"
                           pattern="[0-9]{10}">
                    <label for="telefono">Teléfono</label>
                    <div class="invalid-feedback">
                        Por favor ingrese un número de teléfono válido (10 dígitos)
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h5>Información de Contacto</h5>
                <div class="form-floating mb-3">
                    <input type="text" 
                           class="form-control" 
                           id="direccion" 
                           name="direccion" 
                           placeholder="Dirección"
                           value="<?= htmlspecialchars($cliente['direccion']) ?>">
                    <label for="direccion">Dirección</label>
                </div>

                <div class="form-floating">
                    <input type="email" 
                           class="form-control" 
                           id="correo" 
                           name="correo" 
                           placeholder="Correo electrónico"
                           value="<?= htmlspecialchars($cliente['correo']) ?>">
                    <label for="correo">Correo electrónico</label>
                    <div class="invalid-feedback">
                        Por favor ingrese un correo electrónico válido
                    </div>
                </div>
            </div>

            <div class="buttons-container">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="fas fa-save me-2"></i>Guardar Cambios
                </button>
                <a href="clientes.php" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../footer.php'; ?>
<script src="../../assets/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://kit.fontawesome.com/your-font-awesome-kit.js"></script>

<script>
(function () {
    'use strict'
    
    const forms = document.querySelectorAll('.needs-validation')
    
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })

    // Formato de teléfono
    const telefonoInput = document.getElementById('telefono')
    telefonoInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '')
        if (value.length > 10) value = value.slice(0, 10)
        e.target.value = value
    })
})()
</script>

</body>
</html>