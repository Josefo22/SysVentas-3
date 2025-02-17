<?php
require_once "../../db.php";
session_start();

$query = $conn->query("SELECT p.id, p.nombre, p.descripcion, p.precio, p.stock, p.activo, c.nombre AS categoria, p.created_at 
                        FROM productos p 
                        LEFT JOIN categorias c ON p.categoria_id = c.id 
                        ORDER BY p.id DESC");
$productos = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Productos</title>
    <link rel="stylesheet" href="../../style/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
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

        .btn-secondary {
            background-color: #95a5a6;
            border: none;
        }

        .btn-secondary:hover {
            background-color: #7f8c8d;
            transform: translateY(-2px);
        }

        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }

        #productosTable {
            width: 100% !important;
        }

        #productosTable thead th {
            background-color: #34495e;
            color: white;
            padding: 12px;
        }

        #productosTable tbody td {
            padding: 12px;
            vertical-align: middle;
        }

        .btn-toggle {
            padding: 5px 10px;
            font-size: 0.875rem;
            transition: all 0.3s;
        }

        .action-column {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.875rem;
        }

        .stock-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-weight: 600;
        }

        .stock-low {
            background-color: #ffecb3;
            color: #ff8f00;
        }

        .stock-medium {
            background-color: #c8e6c9;
            color: #2e7d32;
        }

        .stock-high {
            background-color: #bbdefb;
            color: #1976d2;
        }

        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px 10px;
        }

        .dt-buttons {
            margin-bottom: 15px;
        }

        .dt-button {
            background-color: #f8f9fa !important;
            border: 1px solid #ddd !important;
            border-radius: 4px !important;
            padding: 5px 15px !important;
            margin-right: 5px !important;
        }

        .dt-button:hover {
            background-color: #e9ecef !important;
        }
    </style>
</head>
<body>

<?php include '../navbar.php'; ?>

<div class="page-header">
    <div class="container">
        <h2 class="text-center page-title">
            <i class="fas fa-boxes me-2"></i>Gestión de Productos
        </h2>
        <div class="d-flex justify-content-between action-buttons">
            <a href="agregar_producto.php" class="btn btn-success">
                <i class="fas fa-plus me-2"></i>Agregar Producto
            </a>
            <a href="crear_categoria.php" class="btn btn-secondary">
                <i class="fas fa-tags me-2"></i>Crear Categoría
            </a>
        </div>
    </div>
</div>

<div class="container">
    <div class="table-container">
        <table id="productosTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Categoría</th>
                    <th>Fecha Creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $producto): ?>
                    <tr>
                        <td><?= $producto['id'] ?></td>
                        <td><?= htmlspecialchars($producto['nombre']) ?></td>
                        <td><?= htmlspecialchars($producto['descripcion']) ?></td>
                        <td>
                            <span class="fw-bold">$<?= number_format($producto['precio'], 2) ?></span>
                        </td>
                        <td>
                            <?php
                            $stockClass = '';
                            if ($producto['stock'] <= 10) {
                                $stockClass = 'stock-low';
                            } elseif ($producto['stock'] <= 50) {
                                $stockClass = 'stock-medium';
                            } else {
                                $stockClass = 'stock-high';
                            }
                            ?>
                            <span class="stock-badge <?= $stockClass ?>">
                                <?= $producto['stock'] ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-info text-dark">
                                <?= htmlspecialchars($producto['categoria']) ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y', strtotime($producto['created_at'])) ?></td>
                        <td class="action-column">
                            <a href="editar_producto.php?id=<?= $producto['id'] ?>" 
                               class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="eliminar_producto.php?id=<?= $producto['id'] ?>" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirm('¿Seguro que deseas eliminar este producto?');">
                                <i class="fas fa-trash"></i>
                            </a>
                            <button class="btn btn-sm btn-toggle <?= $producto['activo'] ? 'btn-success' : 'btn-danger' ?>" 
                                    onclick="toggleEstado(<?= $producto['id'] ?>, this)">
                                <i class="fas <?= $producto['activo'] ? 'fa-check' : 'fa-times' ?>"></i>
                                <?= $producto['activo'] ? 'Activo' : 'Inactivo' ?>
                            </button>
                            <a href="añadir_stock.php?id=<?= $producto['id'] ?>" 
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Stock
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../footer.php'; ?>
<script src="../../assets/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
$(document).ready(function() {
    $('#productosTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'copy',
                text: '<i class="fas fa-copy"></i> Copiar',
                className: 'btn btn-secondary'
            },
            {
                extend: 'csv',
                text: '<i class="fas fa-file-csv"></i> CSV',
                className: 'btn btn-secondary'
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: 'btn btn-secondary'
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: 'btn btn-secondary'
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> Imprimir',
                className: 'btn btn-secondary'
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/Spanish.json'
        }
    });
});

function toggleEstado(id, btn) {
    fetch(`cambiar_estado.php?id=${id}`)
    .then(response => response.text())
    .then(data => {
        if (data.trim() === "ok") {
            let activo = btn.classList.contains("btn-success");
            btn.classList.toggle("btn-success", !activo);
            btn.classList.toggle("btn-danger", activo);
            const icon = btn.querySelector('i');
            icon.classList.toggle("fa-check", !activo);
            icon.classList.toggle("fa-times", activo);
            btn.textContent = activo ? " Inactivo" : " Activo";
            btn.prepend(icon);
        } else {
            alert("Error al cambiar estado.");
        }
    })
    .catch(error => console.error("Error:", error));
}
</script>

</body>
</html>