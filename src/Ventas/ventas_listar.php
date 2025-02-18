<?php
require_once "../../db.php"; // Asegúrate de tener la conexión a la base de datos

// Obtener todas las ventas
$stmt = $conn->prepare("SELECT v.id, v.total, c.nombre AS cliente, v.fecha 
                        FROM ventas v 
                        JOIN clientes c ON v.cliente_id = c.id 
                        ORDER BY v.fecha DESC");
$stmt->execute();
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Ventas</title>
    <link rel="stylesheet" href="../../style/style.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.dataTables.min.css">
    <!-- Font Awesome -->
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
        
        .btn-primary {
            background-color: #3498db;
            border: none;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
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

        .btn-info {
            background-color: #3498db;
            border: none;
            color: white;
        }

        .btn-info:hover {
            background-color: #2980b9;
            color: white;
            transform: translateY(-2px);
        }

        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }

        .card-header {
            border-radius: 8px 8px 0 0 !important;
            background-color: #34495e;
            color: white;
            padding: 15px 20px;
        }

        .card-body {
            padding: 25px;
        }

        .table thead th {
            background-color: #f1f3f5;
            color: #2c3e50;
            padding: 12px;
        }

        .table tbody td {
            padding: 12px;
            vertical-align: middle;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: #3498db;
            border-color: #3498db;
            color: white !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #2980b9;
            border-color: #2980b9;
            color: white !important;
        }

        .dt-buttons .btn {
            margin-right: 5px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <?php include '../navbar.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1 class="text-center page-title">
                <i class="fas fa-file-invoice-dollar me-2"></i>Listado de Ventas
            </h1>
            <div class="d-flex justify-content-center action-buttons mt-3">
                <a href="ventas.php" class="btn btn-primary">
                    <i class="fas fa-cash-register me-2"></i>Registrar Nueva Venta
                </a>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <!-- Tarjeta para el listado de ventas -->
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-list me-2"></i>Historial de Ventas</h3>
            </div>
            <div class="card-body">
                <!-- Tabla con los detalles de las ventas -->
                <div class="table-responsive">
                    <table id="ventasTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag me-2"></i>ID Venta</th>
                                <th><i class="fas fa-user me-2"></i>Cliente</th>
                                <th><i class="fas fa-calendar-alt me-2"></i>Fecha</th>
                                <th><i class="fas fa-dollar-sign me-2"></i>Total</th>
                                <th><i class="fas fa-cog me-2"></i>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ventas as $venta): ?>
                                <tr>
                                    <td><?php echo $venta['id']; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-user-circle text-secondary me-2"></i>
                                            <?php echo $venta['cliente']; ?>
                                        </div>
                                    </td>
                                    <td><?php echo $venta['fecha']; ?></td>
                                    <td class="fw-bold">$<?php echo number_format($venta['total'], 2); ?></td>
                                    <td>
                                        <!-- Botón para ver detalles de la venta -->
                                        <a href="detalles_venta.php?venta_id=<?php echo $venta['id']; ?>" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye me-1"></i> Ver Detalles
                                        </a>
                                        <a href="../../assets/pdf/generar.php?venta_id=<?php echo $venta['id']; ?>" class="btn btn-info btn-sm" 
                                               class="btn btn-download btn-sm"
                                               target="_blank">
                                                <i class="fas fa-file-pdf me-1"></i> Factura
                                            </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="../../assets/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <script>
        $(document).ready(function() {
            $('#ventasTable').DataTable({
                dom: 'Bfrtip',
                buttons: [
                    {
                        extend: 'copy',
                        text: '<i class="fas fa-copy me-1"></i> Copiar',
                        className: 'btn btn-secondary'
                    },
                    {
                        extend: 'csv',
                        text: '<i class="fas fa-file-csv me-1"></i> CSV',
                        className: 'btn btn-secondary'
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel me-1"></i> Excel',
                        className: 'btn btn-secondary'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf me-1"></i> PDF',
                        className: 'btn btn-secondary'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print me-1"></i> Imprimir',
                        className: 'btn btn-secondary'
                    }
                ],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/Spanish.json'
                },
                responsive: true,
                order: [[0, 'desc']],
                columnDefs: [
                    {targets: 4, orderable: false}
                ]
            });
        });
    </script>
</body>
</html>