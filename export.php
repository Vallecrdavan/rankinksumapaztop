<?php
session_start();
require_once "config.php";

// Verificar si el usuario está logueado y es admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Configurar cabeceras para descarga de CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="calificaciones_cafe_sumapaz.csv"');

// Crear el archivo CSV
$output = fopen('php://output', 'w');

// Escribir la línea de encabezado UTF-8 BOM para Excel
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Encabezados de columnas
fputcsv($output, [
    'ID', 'Usuario', 'Sabor', 'Aroma', 'Cuerpo', 'Acidez', 'Amargor', 
    'Balance', 'Intensidad', 'Postgusto', 'Sostenibilidad', 
    'Origen', 'Marca', 'Comentario', 'Fecha'
]);

// Obtener datos
try {
    $sql = "SELECT responses.id, users.nombre, responses.sabor, responses.aroma, responses.cuerpo, 
                   responses.acidez, responses.amargor, responses.balance, responses.intensidad, 
                   responses.postgusto, responses.sostenibilidad, responses.origen, responses.marca, responses.comentario,
                   responses.created_at
            FROM responses
            JOIN users ON responses.user_id = users.id
            ORDER BY responses.id DESC";
    $stmt = $conn->query($sql);
    
    // Escribir cada fila
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['id'],
            $row['nombre'],
            $row['sabor'],
            $row['aroma'],
            $row['cuerpo'],
            $row['acidez'],
            $row['amargor'],
            $row['balance'],
            $row['intensidad'],
            $row['postgusto'],
            $row['sostenibilidad'],
            $row['origen'],
            $row['marca'],
            $row['comentario'],
            $row['created_at']
        ]);
    }
} catch (PDOException $e) {
    die("Error al exportar datos: " . $e->getMessage());
}

fclose($output);
exit();
?>