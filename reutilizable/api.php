<?php
// Controlador frontal de la API
require_once __DIR__ . '/../rest_simple/db.php';

// Tipo de contenido de respuesta
header('Content-Type: application/json; charset=utf-8');

// Método HTTP
$metodo = $_SERVER['REQUEST_METHOD'];

// Leer cuerpo crudo de la petición
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

// Si no hay datos JSON, intentar limpiar posibles caracteres invisibles
if (!$input) {
    $raw = trim($raw, "\xEF\xBB\xBF");
    $input = json_decode($raw, true);
}

// Si aún no hay datos, usar $_POST (form-data o x-www-form-urlencoded)
if (!$input) {
    $input = $_POST;
}

// --- DESCOMENTA ESTAS LÍNEAS PARA DEPURAR ---
// var_dump($input);
// exit;

// Parámetros GET
$id = $_GET['id'] ?? null;
$endpoint = $_GET['endpoint'] ?? 'productos';

// Verificar que el endpoint sea válido
if ($endpoint != 'productos') {
    http_response_code(404);
    echo json_encode(['error' => 'Recurso no encontrado']);
    exit;
}

// Funciones CRUD
function lista($pdo)
{
    $stmt = $pdo->prepare("SELECT * FROM productos");
    $stmt->execute();
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
}

function ver($pdo, $id)
{
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(['error' => 'Producto no encontrado']);
        exit;
    }

    echo json_encode($row);
}

function crear($pdo, $input)
{
    if (!isset($input['nombre']) || !isset($input['precio'])) {
        echo json_encode(['error' => 'Faltan datos']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO productos (nombre, precio) VALUES (?, ?)");
    $stmt->execute([$input['nombre'], $input['precio']]);

    echo json_encode([
        'mensaje' => 'Producto creado correctamente',
        'id' => $pdo->lastInsertId()
    ]);
}

// Router de peticiones
switch ($metodo) {
    case 'GET':
        if ($id != null) {
            ver($pdo, $id);
        } else {
            lista($pdo);
        }
        break;

    case 'POST':
        crear($pdo, $input);
        break;

    default:
        http_response_code(405);
        echo json_encode(['mensaje' => 'Método no permitido']);
        break;
}
?>
