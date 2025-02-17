<?php
require_once "../../db.php";

$term = $_GET['term'];
$stmt = $conn->prepare("SELECT id, nombre, precio, stock 
                       FROM productos WHERE nombre LIKE ? AND stock > 0 LIMIT 10");
$stmt->execute(["%$term%"]);
$productos = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $productos[] = [
        'id' => $row['id'],
        'label' => $row['nombre'] . " - Stock: " . $row['stock'],
        'nombre' => $row['nombre'],
        'precio' => $row['precio'],
        'stock' => $row['stock']
    ];
}

echo json_encode($productos);
?>