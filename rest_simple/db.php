
<?php
//manejo de la base de datos
    $host = 'localhost';
    $dbname = 'soap_demo';
    $username = 'root';
    $password = '';
    $dns = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    try {
        $pdo = new PDO($dns, $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE,
         PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {

        // Manejo de errores de conexión
        die("Error de conexión a la base de datos: " . $e->getMessage());
    }
