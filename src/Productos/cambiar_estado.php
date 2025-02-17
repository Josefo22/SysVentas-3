<?php
require_once "../../db.php"; // Conexión a la base de datos

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("SELECT activo FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto) {
        $nuevo_estado = $producto['activo'] ? 0 : 1;
        $stmt = $conn->prepare("UPDATE productos SET activo = ? WHERE id = ?");
        $stmt->execute([$nuevo_estado, $id]);
        echo "ok";
        exit();
    }
}

echo "error";
?>
