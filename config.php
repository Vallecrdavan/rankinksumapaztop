<?php
// config.php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "coffee_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password);
    // Establecemos el modo de error de PDO a EXCEPTION
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Error en la conexión: " . $e->getMessage();
    exit();
}
?>
