<?php
require_once "../../db.php";

$term = $_GET['term'] ?? '';

try {
    $stmt = $conn->prepare("SELECT id, nombre, telefono, direccion, correo 
                            FROM clientes 
                            WHERE nombre LIKE ? OR telefono LIKE ? OR correo LIKE ? 
                            LIMIT 10");
    $searchTerm = "%$term%";
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($clientes as $cliente) {
        $result[] = [
            'id'        => $cliente['id'],
            'label'     => $cliente['nombre'] . ' - ' . $cliente['telefono'], // Lo que se muestra en el autocompletado
            'nombre'    => $cliente['nombre'],
            'telefono'  => $cliente['telefono'],
            'direccion' => $cliente['direccion'],
            'correo'    => $cliente['correo']
        ];
    }

    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error en la consulta: ' . $e->getMessage()]);
}
?>
