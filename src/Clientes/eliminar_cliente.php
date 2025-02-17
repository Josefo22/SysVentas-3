<?php
require_once "../../db.php"; // Conexión a la base de datos

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM clientes WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: clientes.php");
exit();
?>
