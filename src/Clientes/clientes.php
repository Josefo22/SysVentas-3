<?php
require_once "../../db.php";

$query = $conn->query("SELECT * FROM clientes ORDER BY id DESC");
$clientes = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Clientes</title>
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

        .table-container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }

        #clientesTable {
            width: 100% !important;
        }

        #clientesTable thead th {
            background-color: #34495e;
            color: white;
            padding: 12px;
        }

        #clientesTable tbody td {
            padding: 12px;
            vertical-align: middle;
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

        .btn-edit {
            background-color: #f39c12;
            border: none;
            color: white;
        }

        .btn-edit:hover {
            background-color: #e67e22;
            transform: translateY(-2px);
        }

        .btn-delete {
            background-color: #e74c3c;
            border: none;
        }

        .btn-delete:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
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

        .contact-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-weight: 500;
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .date-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-weight: 500;
            background-color: #e3f2fd;
            color: #1976d2;
        }
    </style>
</head>
<body>

<?php include '../navbar.php'; ?>

<div class="page-header">
    <div class="container">
        <h2 class="text-center page-title">
            <i class="fas fa-users me-2"></i>Gestión de Clientes
        </h2>
        <div class="d-flex justify-content-center action-buttons">
            <a href="agregar_cliente.php" class="btn btn-success">
                <i class="fas fa-user-plus me-2"></i>Añadir Cliente
            </a>
        </div>
    </div>
</div>

<div class="container">
    <div class="table-container">
        <table id="clientesTable" class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>Correo</th>
                    <th>Fecha Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td><?= $cliente['id'] ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-circle me-2 text-secondary"></i>
                                <?= htmlspecialchars($cliente['nombre']) ?>
                            </div>
                        </td>
                        <td>
                            <span class="contact-badge">
                                <i class="fas fa-phone me-1"></i>
                                <?= htmlspecialchars($cliente['telefono']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($cliente['direccion']) ?></td>
                        <td>
                            <span class="contact-badge">
                                <i class="fas fa-envelope me-1"></i>
                                <?= htmlspecialchars($cliente['correo']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="date-badge">
                                <i class="fas fa-calendar me-1"></i>
                                <?= date('d/m/Y', strtotime($cliente['created_at'])) ?>
                            </span>
                        </td>
                        <td class="action-column">
                            <a href="editar_cliente.php?id=<?= $cliente['id'] ?>" 
                               class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="eliminar_cliente.php?id=<?= $cliente['id'] ?>" 
                               class="btn btn-danger btn-sm" 
                               onclick="return confirm('¿Seguro que deseas eliminar este cliente?');">
                                <i class="fas fa-trash"></i>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

<script>
$(document).ready(function() {
    $('#clientesTable').DataTable({
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
</script>

</body>
</html>