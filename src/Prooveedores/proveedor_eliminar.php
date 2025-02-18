<?php
// Incluir el archivo de conexión a la base de datos
require_once "../../db.php"; // Asegúrate de tener la conexión a la base de datos

// Verificar si se pasó el parámetro id en la URL
if (isset($_GET['id'])) {
    // Obtener el id del proveedor a eliminar
    $id = $_GET['id'];

    // Preparar la consulta SQL para eliminar el proveedor con el id especificado
    $sql = "DELETE FROM proveedores WHERE id = :id";

    try {
        // Preparar la sentencia
        $stmt = $conn->prepare($sql);
        
        // Enlazar el parámetro id
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            // Si la eliminación fue exitosa, redirigir a la página de proveedores
            header("Location: proveedores.php?mensaje=El proveedor se eliminó correctamente");
            exit();
        } else {
            // Si hubo un error, mostrar un mensaje
            echo "Error al eliminar el proveedor.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "ID no proporcionado.";
}
?>
