<?php
require_once "../../db.php";


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID de venta no válido');
}

$ventaId = (int)$_GET['id'];

try {
    // Obtener detalles de la venta
    $stmt = $conn->prepare("
        SELECT dv.*, p.nombre as producto_nombre
        FROM detalle_venta dv
        JOIN productos p ON dv.producto_id = p.id
        WHERE dv.venta_id = ?
    ");
    $stmt->execute([$ventaId]);
    $detalles = $stmt->fetchAll();
    
    // Obtener información de la venta
    $stmt = $conn->prepare("
        SELECT v.*, c.nombre as cliente_nombre, u.nombre as usuario_nombre
        FROM ventas v
        LEFT JOIN clientes c ON v.cliente_id = c.id
        LEFT JOIN usuarios u ON v.usuario_id = u.id
        WHERE v.id = ?
    ");
    $stmt->execute([$ventaId]);
    $venta = $stmt->fetch();
    
    if (!$venta) {
        die('Venta no encontrada');
    }
?>
<?php include '../navbar.php'; ?>
    <div class="container-fluid">
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Cliente:</strong> <?php echo htmlspecialchars($venta['cliente_nombre'] ?? 'Cliente General'); ?><br>
                <strong>Usuario:</strong> <?php echo htmlspecialchars($venta['usuario_nombre']); ?><br>
                <strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($venta['fecha'])); ?>
            </div>
            <div class="col-md-6 text-end">
                <strong>Venta #:</strong> <?php echo $venta['id']; ?><br>
                <strong>Total:</strong> $<?php echo number_format($venta['total'], 2); ?>
            </div>
        </div>
        
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="text-end">Cantidad</th>
                    <th class="text-end">Precio Unitario</th>
                    <th class="text-end">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $detalle): ?>
                <tr>
                    <td><?php echo htmlspecialchars($detalle['producto_nombre']); ?></td>
                    <td class="text-end"><?php echo $detalle['cantidad']; ?></td>
                    <td class="text-end">$<?php echo number_format($detalle['precio'], 2); ?></td>
                    <td class="text-end">$<?php echo number_format($detalle['subtotal'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="fw-bold">
                    <td colspan="3" class="text-end">Total:</td>
                    <td class="text-end">$<?php echo number_format($venta['total'], 2); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>
    <script src="../../assets/bootstrap.bundle.min.js"></script>
    <?php include '../footer.php'; ?>
<?php
} catch (PDOException $e) {
    die('Error al cargar los detalles: ' . $e->getMessage());
}
?>
