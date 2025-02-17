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
        /* Estilo básico para el botón */
.btn {
    background-color: #4CAF50; /* Verde */
    color: white;
    padding: 10px 20px;
    text-align: center;
    text-decoration: none;
    display: inline-block;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    font-size: 16px;
}

/* Efecto al pasar el ratón por encima */
.btn:hover {
    background-color: #45a049;
}

    </style>
</head>
<body>
    <?php include '../navbar.php'; ?>
    <div class="container mt-5">
    <!-- Fila para los botones -->
    <div class="row mb-4">
        <div class="col-md-6">
            <!-- Botón para listar ventas -->
            <form action="ventas_listar.php" method="get">
                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="fas fa-list me-2"></i>Ver Listado de Ventas
                </button>
            </form>
        </div>
    </div>

    <!-- Tarjeta para la nueva venta -->
    <div class="card shadow-lg">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0"><i class="fas fa-cash-register me-2"></i>Nueva Venta</h3>
        </div>
            <div class="card-body">
                <form id="ventaForm" method="post" action="procesar_venta.php">
                    <!-- Sección Cliente -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label class="form-label">Buscar Cliente:</label>
                            <div class="input-group">
                                <input type="text" id="buscarCliente" class="form-control" 
                                       placeholder="Escriba el nombre del cliente...">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                            </div>
                            <input type="hidden" name="cliente_id" id="cliente_id">
                            <div class="mt-2" id="infoCliente"></div>
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
                            <input type="number" id="cantidad" class="form-control" min="1" value="1">
                        </div>
                    </div>

                    <!-- Lista de Productos Seleccionados -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered">
                            <thead class="table-light">
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
                                    <h5 class="card-title">Total de la Venta</h5>
                                    <div class="h4 text-primary" id="totalVenta">$0.00</div>
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
<br>
    <?php include '../footer.php'; ?>

    <!-- Scripts -->
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
                $('#nombreCliente').val(ui.item.nombre);
                $('#telefonoCliente').val(ui.item.telefono);
                $('#direccionCliente').val(ui.item.direccion);
                $('#correoCliente').val(ui.item.correo);

                // Mostrar información del cliente seleccionado
                $('#infoCliente').html(`
                    <div class="alert alert-info p-2">
                        <strong>Cliente seleccionado:</strong> ${ui.item.nombre}<br>
                        <strong>Teléfono:</strong> ${ui.item.telefono}<br>
                        <strong>Dirección:</strong> ${ui.item.direccion}<br>
                        <strong>Correo:</strong> ${ui.item.correo}
                    </div>
                `);
            }
        });

        // Autocompletado para Productos
        $('#buscarProducto').autocomplete({
            source: 'buscar_productos.php',
            minLength: 2,
            select: function(event, ui) {
                agregarProducto(ui.item);
                $('#buscarProducto').val('');
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
            $('#productos').val(JSON.stringify(productos)); // Aquí llenamos el campo oculto
        }

        // Función para agregar productos a la tabla
        function agregarProducto(producto) {
            const cantidad = $('#cantidad').val();
            const subtotal = (producto.precio * cantidad).toFixed(2);
            
            const nuevaFila = `
                <tr data-id="${producto.id}">
                    <td>${producto.nombre}</td>
                    <td>$${producto.precio}</td>
                    <td>${cantidad}</td>
                    <td>$${subtotal}</td>
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
