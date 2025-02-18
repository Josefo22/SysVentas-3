<?php
require_once "../../db.php"; // Conexión a la base de datos

$term = $_GET['term'] ?? '';

$stmt = $conn->prepare("SELECT id, nombre AS label FROM categorias WHERE nombre LIKE ? LIMIT 10");
$stmt->execute(["%$term%"]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result);
?>
