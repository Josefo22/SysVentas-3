<?php
session_start();
require_once "../../db.php";

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los productos desde el formulario
    if (!isset($_POST['productos']) || empty($_POST['productos'])) {
        echo json_encode(["status" => "error", "message" => "No se recibieron productos."]);
        exit();
    }

    $productos = json_decode($_POST['productos'], true);
    $cliente_id = $_POST['cliente_id'];
    $total = $_POST['total'];

    // Validaciones
    if (empty($productos)) {
        echo json_encode(["status" => "error", "message" => "No hay productos en la venta."]);
        exit();
    }

    // Iniciar la transacción
    try {
        // Comenzar la transacción
        $conn->beginTransaction();

        // Insertar la venta en la base de datos (tabla ventas)
        $stmt = $conn->prepare("INSERT INTO ventas (cliente_id, total, fecha) VALUES (?, ?, NOW())");
        $stmt->execute([$cliente_id, $total]);

        // Obtener el ID de la venta recién insertada
        $venta_id = $conn->lastInsertId();

        // Insertar los productos de la venta en la tabla detalle_venta
        foreach ($productos as $producto) {
            $stmt = $conn->prepare("INSERT INTO detalle_venta (venta_id, producto_id, cantidad, precio, subtotal) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $venta_id,
                $producto['id'],
                $producto['cantidad'],
                $producto['precio'],
                $producto['subtotal']
            ]);
        }

        // Confirmar la transacción
        $conn->commit();

        // Redirigir al script que genera el PDF de la factura
        header("Location: ../../assets/pdf/generar.php?venta_id=" . $venta_id);
        exit();

    } catch (Exception $e) {
        // Si ocurre un error, revertir la transacción
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        // Loguear el error para depuración
        error_log("Error al procesar la venta: " . $e->getMessage());
        error_log("Detalles de la venta: " . print_r($productos, true));

        // Mostrar mensaje de error al usuario
        echo json_encode(["status" => "error", "message" => "Hubo un error al procesar la venta. Verifica los logs para más detalles."]);
        exit();
    }
} else {
    echo json_encode(["status" => "error", "message" => "Método no permitido."]);
    exit();
}
?>
