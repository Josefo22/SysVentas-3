<?php
$host = "localhost";
$dbname = "farmacia_db";
$username = "root";  // Cambia si tienes otro usuario
$password = "";      // Cambia si tienes contraseña en tu MySQL

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
