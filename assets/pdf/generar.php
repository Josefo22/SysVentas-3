<?php
require_once "fpdf/fpdf.php";
require_once "../../db.php";

if (!isset($_GET['venta_id'])) {
    die("ID de venta no proporcionado.");
}

$venta_id = $_GET['venta_id'];

// Obtener datos de la venta
$stmt = $conn->prepare("SELECT v.id, v.total, c.nombre AS cliente, v.fecha 
                        FROM ventas v 
                        JOIN clientes c ON v.cliente_id = c.id 
                        WHERE v.id = ?");
$stmt->execute([$venta_id]);
$venta = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$venta) {
    die("Venta no encontrada.");
}

// Obtener detalles de la venta
$stmt = $conn->prepare("SELECT p.nombre, d.cantidad, d.precio, d.subtotal 
                        FROM detalle_venta d 
                        JOIN productos p ON d.producto_id = p.id 
                        WHERE d.venta_id = ?");
$stmt->execute([$venta_id]);
$detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Crear PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Factura de Venta', 0, 1, 'C');

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(100, 10, "Cliente: " . $venta['cliente'], 0, 1);
$pdf->Cell(100, 10, "Fecha: " . $venta['fecha'], 0, 1);
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(80, 10, 'Producto', 1);
$pdf->Cell(30, 10, 'Cantidad', 1);
$pdf->Cell(30, 10, 'Precio', 1);
$pdf->Cell(30, 10, 'Subtotal', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
foreach ($detalles as $item) {
    $pdf->Cell(80, 10, $item['nombre'], 1);
    $pdf->Cell(30, 10, $item['cantidad'], 1, 0, 'C');
    $pdf->Cell(30, 10, number_format($item['precio'], 2), 1, 0, 'C');
    $pdf->Cell(30, 10, number_format($item['subtotal'], 2), 1, 0, 'C');
    $pdf->Ln();
}

// Total
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(140, 10, 'Total', 1);
$pdf->Cell(30, 10, number_format($venta['total'], 2), 1, 0, 'C');

$pdf->Output();
?>
