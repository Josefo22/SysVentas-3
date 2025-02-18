<?php
session_start();
require_once "../../db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input data
    if (!isset($_POST['productos']) || !isset($_POST['cliente_id']) || !isset($_POST['total'])) {
        echo json_encode(["status" => "error", "message" => "No se recibieron los datos necesarios."]);
        exit();
    }

    $productos = json_decode($_POST['productos'], true);
    $cliente_id = $_POST['cliente_id'];
    $total = $_POST['total'];

    // Validate products
    if (empty($productos)) {
        echo json_encode(["status" => "error", "message" => "No hay productos en la venta."]);
        exit();
    }

    try {
        // Begin transaction
        $conn->beginTransaction();

        // Insert sale
        $stmt = $conn->prepare("INSERT INTO ventas (cliente_id, total, fecha) VALUES (?, ?, NOW())");
        $stmt->execute([$cliente_id, $total]);
        $venta_id = $conn->lastInsertId();

        // Process each product
        foreach ($productos as $producto) {
            // Verify current stock
            $stmt = $conn->prepare("SELECT stock FROM productos WHERE id = ? FOR UPDATE");
            $stmt->execute([$producto['id']]);
            $current_stock = $stmt->fetchColumn();

            // Validate if there's enough stock
            if ($current_stock < $producto['cantidad']) {
                throw new Exception("Stock insuficiente para el producto ID: " . $producto['id']);
            }

            // Update product stock
            $stmt = $conn->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$producto['cantidad'], $producto['id']]);

            // Insert sale detail
            $stmt = $conn->prepare("INSERT INTO detalle_venta (venta_id, producto_id, cantidad, precio, subtotal) 
                                  VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $venta_id,
                $producto['id'],
                $producto['cantidad'],
                $producto['precio'],
                $producto['subtotal']
            ]);
        }

        // Commit transaction
        $conn->commit();

        // Redirect to PDF generation
        header("Location: ../../assets/pdf/generar.php?venta_id=" . $venta_id);
        exit();

    } catch (Exception $e) {
        // Rollback on error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        // Log error
        error_log("Error al procesar la venta: " . $e->getMessage());
        error_log("Detalles de la venta: " . print_r($productos, true));

        // Return error message
        echo json_encode([
            "status" => "error", 
            "message" => "Error al procesar la venta: " . $e->getMessage()
        ]);
        exit();
    }
} else {
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
    exit();
}
?>