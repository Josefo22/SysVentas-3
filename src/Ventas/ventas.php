<?php
session_start();
require_once "../../db.php";

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Obtener clientes para autocompletado
$sqlClientes = "SELECT id, nombre, telefono FROM clientes";
$stmtClientes = $conn->query($sqlClientes);
$clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro de Ventas</title>
    <link rel="stylesheet" href="../../style/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.0/themes/base/jquery-ui.css">
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
        
        .btn-primary {
            background-color: #3498db;
            border: none;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }

        .btn-danger {
            background-color: #e74c3c;
            border: none;
        }

        .btn-danger:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
        }

        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
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

        .form-label {
            font-weight: 500;
            color: #2c3e50;
        }

        .form-control {
            border-radius: 5px;
            padding: 10px 12px;
            border: 1px solid #ddd;
        }

        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(52, 73, 94, 0.25);
            border-color: #34495e;
        }

        .input-group-text {
            background-color: #f1f3f5;
            border: 1px solid #ddd;
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

        .alert-info {
            background-color: #e8f4f8;
            border-color: #d1e7f0;
            color: #0c5460;
        }

        .badge-primary {
            background-color: #3498db;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-weight: 500;
        }

        .badge-success {
            background-color: #2ecc71;
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-weight: 500;
        }

        .border-primary {
            border-color: #3498db !important;
        }

        .text-primary {
            color: #3498db !important;
        }
    </style>
</head>
<body>
    <?php include '../navbar.php'; ?>
    
    <div class="page-header">
        <div class="container">
            <h2 class="text-center page-title">
                <i class="fas fa-cash-register me-2"></i>Registro de Ventas
            </h2>
            <div class="d-flex justify-content-center action-buttons">
                <a href="ventas_listar.php" class="btn btn-primary">
                    <i class="fas fa-list me-2"></i>Ver Listado de Ventas
                </a>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <!-- Tarjeta para la nueva venta -->
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Nueva Venta</h3>
            </div>
            <div class="card-body">
                <form id="ventaForm" method="post" action="procesar_venta.php">
                    <!-- Sección Cliente -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label class="form-label">Buscar Cliente:</label>
                            <div class="input-group mb-2">
                                <input type="text" id="buscarCliente" class="form-control" 
                                       placeholder="Escriba el nombre del cliente...">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                            </div>
                            <input type="hidden" name="cliente_id" id="cliente_id">
                            <div class="mt-3" id="infoCliente"></div>
                        </div>
                    </div>

                    <!-- Sección Productos -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <label class="form-label">Buscar Producto:</label>
                            <div class="input-group">
                                <input type="text" id="buscarProducto" class="form-control" 
                                       placeholder="Escriba el nombre del producto...">
                                <span class="input-group-text"><i class="fas fa-box"></i></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Cantidad:</label>
                            <div class="input-group">
                                <input type="number" id="cantidad" class="form-control" min="1" value="1">
                                <button type="button" id="agregarBtn" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>Agregar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Productos Seleccionados -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Precio Unitario</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="productosSeleccionados">
                                <!-- Productos se añaden dinámicamente aquí -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Totales -->
                    <div class="row justify-content-end">
                        <div class="col-md-4">
                            <div class="card border-primary">
                                <div class="card-body">
                                    <h5 class="card-title text-primary">
                                        <i class="fas fa-calculator me-2"></i>Total de la Venta
                                    </h5>
                                    <div class="h3 text-primary fw-bold" id="totalVenta">$0.00</div>
                                    <input type="hidden" name="total" id="total">
                                    <input type="hidden" name="productos" id="productos">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-check-circle me-2"></i>Finalizar Venta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>

    <!-- Scripts -->
    <script src="../../assets/bootstrap.bundle.min.js"></script>
    <script src="../../assets/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.min.js"></script>
    <script>
     $(function() {
        // Autocompletado para Clientes
        $('#buscarCliente').autocomplete({
            source: 'buscar_clientes.php',
            minLength: 2,
            select: function(event, ui) {
                $('#cliente_id').val(ui.item.id);
                
                // Mostrar información del cliente seleccionado
                $('#infoCliente').html(`
                    <div class="alert alert-info p-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-user-circle fs-4 me-2"></i>
                            <strong class="fs-5">${ui.item.nombre}</strong>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1">
                                    <i class="fas fa-phone me-2"></i>
                                    <span class="badge-primary">${ui.item.telefono}</span>
                                </p>
                                <p class="mb-1">
                                    <i class="fas fa-envelope me-2"></i>
                                    <span class="badge-primary">${ui.item.correo || 'No disponible'}</span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1">
                                    <i class="fas fa-map-marker-alt me-2"></i>
                                    ${ui.item.direccion || 'No disponible'}
                                </p>
                            </div>
                        </div>
                    </div>
                `);
            }
        });

        // Autocompletado para Productos
        let productoSeleccionado = null;
        
        $('#buscarProducto').autocomplete({
            source: 'buscar_productos.php',
            minLength: 2,
            select: function(event, ui) {
                productoSeleccionado = ui.item;
                return false;
            }
        });

        // Botón para agregar producto
        $('#agregarBtn').click(function() {
            if (productoSeleccionado) {
                agregarProducto(productoSeleccionado);
                $('#buscarProducto').val('');
                productoSeleccionado = null;
            } else {
                alert('Por favor, seleccione un producto primero');
            }
        });

        function actualizarTotal() {
            let total = 0;
            let productos = [];
            
            $('#productosSeleccionados tr').each(function() {
                const id = $(this).data('id');
                const nombre = $(this).find('td:eq(0)').text();
                const precio = parseFloat($(this).find('td:eq(1)').text().replace('$', ''));
                const cantidad = parseInt($(this).find('td:eq(2)').text());
                const subtotal = parseFloat($(this).find('td:eq(3)').text().replace('$', ''));
                
                productos.push({
                    id: id,
                    nombre: nombre,
                    precio: precio,
                    cantidad: cantidad,
                    subtotal: subtotal
                });

                total += subtotal;
            });
            
            $('#totalVenta').text('$' + total.toFixed(2));
            $('#total').val(total.toFixed(2));

            // Asignar los productos al campo oculto
            $('#productos').val(JSON.stringify(productos));
        }

        // Función para agregar productos a la tabla
        function agregarProducto(producto) {
            const cantidad = $('#cantidad').val();
            const subtotal = (producto.precio * cantidad).toFixed(2);
            
            const nuevaFila = `
                <tr data-id="${producto.id}">
                    <td>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-box text-secondary me-2"></i>
                            ${producto.nombre}
                        </div>
                    </td>
                    <td>$${producto.precio}</td>
                    <td>${cantidad}</td>
                    <td class="fw-bold">$${subtotal}</td>
                    <td>
                        <button class="btn btn-danger btn-sm remover-producto">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            
            $('#productosSeleccionados').append(nuevaFila);
            actualizarTotal();
        }

        // Eliminar producto de la tabla
        $(document).on('click', '.remover-producto', function() {
            $(this).closest('tr').remove();
            actualizarTotal();
        });

    });
    </script>
</body>
</html>