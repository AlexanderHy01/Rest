<?php
require_once __DIR__ . '/../dao/ProductoDao.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=soap_demo;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos', 'detalle' => $e->getMessage()]);
    exit;
}

$productoDao = new ProductoDao($pdo);

// Cabeceras CORS y tipo de contenido
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Responder inmediatamente a preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, '/');
$pares = explode('/', $path);
$recurso = $pares[0] ?? '';
$id = isset($pares[1]) && is_numeric($pares[1]) ? (int)$pares[1] : null;

// Leer input JSON solo si hay contenido
$input = json_decode(file_get_contents('php://input'), true);

try {
    if ($recurso !== 'productos') {
        http_response_code(404);
        echo json_encode(['error' => 'Recurso no encontrado']);
        exit;
    }

    switch ($method) {
        case 'GET':
            if ($id !== null) {
                $p = $productoDao->porId($id);
                if (!$p) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Producto no encontrado']);
                } else {
                    echo json_encode($p);
                }
            } else {
                $productos = $productoDao->obtenerTodos();
                echo json_encode($productos);
            }
            break;

        case 'POST':
            if (!$input || !isset($input['nombre'], $input['precio'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Datos incompletos']);
                exit;
            }
            $nuevoId = $productoDao->insertar($input);
            http_response_code(201);
            echo json_encode(['id' => $nuevoId]);
            break;

        case 'PUT':
            if ($id === null || !$input || !isset($input['nombre'], $input['precio'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID y datos completos requeridos']);
                exit;
            }
            $actualizado = $productoDao->actualizar($id, $input['nombre'], $input['precio']);
            if ($actualizado) {
                echo json_encode(['mensaje' => 'Producto actualizado']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Producto no encontrado']);
            }
            break;

        case 'DELETE':
            if ($id === null) {
                http_response_code(400);
                echo json_encode(['error' => 'ID de producto requerido']);
                exit;
            }
            $eliminado = $productoDao->eliminar($id);
            if ($eliminado) {
                echo json_encode(['mensaje' => 'Producto eliminado']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Producto no encontrado']);
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error del servidor', 'detalle' => $e->getMessage()]);
}
