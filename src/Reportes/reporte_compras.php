<?php
require_once "../../db.php";

// Activar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

function formatMoney($amount) {
    return '$' . number_format($amount, 2);
}

// Manejo de petición AJAX para detalles
if (isset($_GET['get_details'])) {
    // Asegurarse de que no haya salida antes del JSON
    ob_clean();
    header('Content-Type: application/json');
    
    try {
        $compra_id = intval($_GET['get_details']);
        
        // Verificar que la compra existe
        $checkStmt = $pdo->prepare("SELECT id FROM compras WHERE id = ?");
        $checkStmt->execute([$compra_id]);
        if (!$checkStmt->fetch()) {
            throw new Exception("Compra no encontrada");
        }

        // Consulta de detalles
        $sql = "
            SELECT 
                dc.compra_id,
                dc.producto_id,
                dc.cantidad,
                dc.precio_unitario,
                dc.subtotal,
                p.nombre as producto_nombre
            FROM detalle_compras dc
            JOIN productos p ON dc.producto_id = p.id
            WHERE dc.compra_id = ?
        ";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$compra_id]);
        $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($detalles)) {
            echo json_encode([
                'status' => 'empty',
                'message' => 'No se encontraron detalles para esta compra'
            ]);
        } else {
            echo json_encode([
                'status' => 'success',
                'data' => $detalles
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
    exit;
}   

// Resto del código HTML permanece igual hasta el script
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Compras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css" rel="stylesheet">
    <style>
        .filters-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .table-container {
            overflow-x: auto;
        }
        .btn-filter {
            margin-top: 32px;
        }
        .modal-lg {
            max-width: 800px;
        }
        .loading {
            text-align: center;
            padding: 20px;
        }
        .error-message {
            color: #dc3545;
            text-align: center;
            padding: 20px;
        }
        .ui-autocomplete {
            z-index: 2000;
            max-height: 200px;
            overflow-y: auto;
            overflow-x: hidden;
        }
        #proveedorWrapper {
            position: relative;
        }
        #clearProveedor {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999;
            display: none;
        }
        @media (max-width: 768px) {
            .filters-section .col-md-3 {
                margin-bottom: 15px;
            }
            .btn-filter {
                margin-top: 0;
            }
        }
    </style>
</head>
<body>
    <?php include '../navbar.php'; ?>
    
    <div class="container mt-4">
        <h2 class="mb-4">Reporte de Compras</h2>
        
        <!-- Filtros -->
        <div class="filters-section">
            <form method="GET" id="filterForm">
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Fecha Inicio:</label>
                        <input type="date" name="fecha_inicio" class="form-control" 
                               value="<?php echo $_GET['fecha_inicio'] ?? ''; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha Fin:</label>
                        <input type="date" name="fecha_fin" class="form-control" 
                               value="<?php echo $_GET['fecha_fin'] ?? ''; ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Proveedor:</label>
                        <div id="proveedorWrapper">
                            <input type="text" id="proveedorNombre" class="form-control" 
                                   value="<?php 
                                   if (!empty($_GET['proveedor_id'])) {
                                       $stmt = $pdo->prepare("SELECT nombre FROM proveedores WHERE id = ?");
                                       $stmt->execute([$_GET['proveedor_id']]);
                                       echo $stmt->fetchColumn();
                                   }
                                   ?>">
                            <input type="hidden" name="proveedor_id" id="proveedor_id" 
                                   value="<?php echo $_GET['proveedor_id'] ?? ''; ?>">
                            <span id="clearProveedor">&times;</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary btn-filter w-100">
                            Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabla de Compras -->
        <div class="table-container">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Proveedor</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $where = [];
                    $params = [];

                    if (!empty($_GET['fecha_inicio'])) {
                        $where[] = "DATE(c.fecha) >= ?";
                        $params[] = $_GET['fecha_inicio'];
                    }
                    if (!empty($_GET['fecha_fin'])) {
                        $where[] = "DATE(c.fecha) <= ?";
                        $params[] = $_GET['fecha_fin'];
                    }
                    if (!empty($_GET['proveedor_id'])) {
                        $where[] = "c.proveedor_id = ?";
                        $params[] = $_GET['proveedor_id'];
                    }

                    $sql = "SELECT c.*, p.nombre as proveedor_nombre 
                            FROM compras c 
                            LEFT JOIN proveedores p ON c.proveedor_id = p.id";
                    
                    if (!empty($where)) {
                        $sql .= " WHERE " . implode(" AND ", $where);
                    }
                    
                    $sql .= " ORDER BY c.fecha DESC";
                    
                    $stmt = $conn->prepare($sql);
                    $stmt->execute($params);
                    
                    $hasResults = false;
                    while ($row = $stmt->fetch()) {
                        $hasResults = true;
                        echo "<tr>";
                        echo "<td>{$row['id']}</td>";
                        echo "<td>" . date('d/m/Y H:i', strtotime($row['fecha'])) . "</td>";
                        echo "<td>{$row['proveedor_nombre']}</td>";
                        echo "<td>" . formatMoney($row['total']) . "</td>";
                    }
                    
                    if (!$hasResults) {
                        echo "<tr><td colspan='5' class='text-center'>No se encontraron resultados</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal de Detalles -->
    <div class="modal fade" id="detallesModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalles de la Compra</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="detallesTableBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>
    <script src="../../assets/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Inicializar autocompletado
        $("#proveedorNombre").autocomplete({
            source: window.location.href,
            minLength: 2,
            select: function(event, ui) {
                $("#proveedor_id").val(ui.item.id);
                $("#clearProveedor").show();
                return true;
            }
        });

        // Limpiar proveedor
        $("#clearProveedor").click(function() {
            $("#proveedorNombre").val('');
            $("#proveedor_id").val('');
            $(this).hide();
        });

        // Mostrar/ocultar botón limpiar
        if ($("#proveedorNombre").val()) {
            $("#clearProveedor").show();
        }

        // Validar formulario
        $("#filterForm").submit(function(e) {
            var fechaInicio = $("input[name='fecha_inicio']").val();
            var fechaFin = $("input[name='fecha_fin']").val();

            if (fechaFin && fechaInicio && fechaFin < fechaInicio) {
                alert("La fecha fin no puede ser menor que la fecha inicio");
                e.preventDefault();
            }
        });
    });

    function mostrarDetalles(compraId) {
    const modal = new bootstrap.Modal(document.getElementById('detallesModal'));
    const tableBody = document.getElementById('detallesTableBody');
    
    // Mostrar estado de carga
    tableBody.innerHTML = `
        <tr>
            <td colspan="4" class="loading">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </td>
        </tr>`;
    
    modal.show();

    // Construir la URL completa
    const url = new URL(window.location.href);
    url.searchParams.set('get_details', compraId);
    
    // Realizar la petición
    fetch(url.toString())
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(response => {
            console.log('Respuesta del servidor:', response); // Para depuración

            if (response.status === 'empty') {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center">
                            ${response.message}
                        </td>
                    </tr>`;
                return;
            }

            if (response.status === 'error') {
                throw new Error(response.message);
            }

            const detalles = response.data;
            let html = '';
            let total = 0;
            
            detalles.forEach(detalle => {
                total += parseFloat(detalle.subtotal);
                html += `
                    <tr>
                        <td>${detalle.producto_nombre}</td>
                        <td class="text-center">${detalle.cantidad}</td>
                        <td class="text-end">${formatMoney(detalle.precio_unitario)}</td>
                        <td class="text-end">${formatMoney(detalle.subtotal)}</td>
                    </tr>`;
            });

            html += `
                <tr class="table-info">
                    <td colspan="3" class="text-end"><strong>Total:</strong></td>
                    <td class="text-end"><strong>${formatMoney(total)}</strong></td>
                </tr>`;

            tableBody.innerHTML = html;
        })
        .catch(error => {
            console.error('Error completo:', error); // Para depuración
            tableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center text-danger">
                        Error al cargar los detalles: ${error.message}<br>
                        <small>Por favor, verifique la consola para más detalles.</small>
                    </td>
                </tr>`;
        });
}

function formatMoney(amount) {
    return '$' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

// Verificar la conexión al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    console.log('Página cargada. Verificando conexión...');
    // Hacer una petición de prueba
    fetch(window.location.href + '?get_details=1')
        .then(response => {
            console.log('Estado de la respuesta:', response.status);
            return response.text();
        })
        .then(text => {
            console.log('Contenido de la respuesta:', text);
        })
        .catch(error => {
            console.error('Error de conexión:', error);
        });
});
    </script>
</body>
</html>