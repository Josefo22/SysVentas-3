<?php
require_once "../../db.php";

if (!isset($_GET['producto_id'])) {
    echo '<div class="alert alert-danger">ID del producto no proporcionado.</div>';
    exit;
}

$producto_id = intval($_GET['producto_id']);

try {
    $stmt = $conn->prepare("SELECT dv.id, dv.cantidad, dv.precio, dv.subtotal, v.fecha, u.nombre AS usuario
                            FROM detalle_venta dv
                            LEFT JOIN ventas v ON dv.venta_id = v.id
                            LEFT JOIN usuarios u ON v.usuario_id = u.id
                            WHERE dv.producto_id = ?
                            ORDER BY v.fecha DESC");
    $stmt->execute([$producto_id]);
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error al obtener movimientos: ' . $e->getMessage() . '</div>';
    exit;
}

if (empty($ventas)) {
    echo '<div class="alert alert-warning">No hay ventas registradas para este producto.</div>';
    exit;
}
?>

<div class="table-responsive">
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>ID Venta</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
                <th>Fecha</th>
                <th>Usuario</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ventas as $venta): ?>
                <tr>
                    <td><?= htmlspecialchars($venta['id']) ?></td>
                    <td><?= htmlspecialchars($venta['cantidad']) ?></td>
                    <td>$<?= number_format($venta['precio'], 2) ?></td>
                    <td>$<?= number_format($venta['subtotal'], 2) ?></td>
                    <td><?= date('d/m/Y H:i', strtotime($venta['fecha'])) ?></td>
                    <td><?= htmlspecialchars($venta['usuario']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="../../assets/bootstrap.bundle.min.js"></script>