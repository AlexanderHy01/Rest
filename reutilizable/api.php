<?php
// Habilitar visualización de errores para debug
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Headers CORS y tipo de contenido
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Origin: *");

// Controlador frontal de la API
require_once __DIR__ . '/../rest_Simple/db.php';

// Verificar la conexión explícitamente
try {
    $test = $pdo->query('SELECT COUNT(*) as count FROM productos');
    $count = $test->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Parámetros de la petición
    $method = $_SERVER['REQUEST_METHOD'];
    $endpoint = $_GET['endpoint'] ?? '';
    $id = $_GET['id'] ?? null;

    // Verificar que estamos en el endpoint correcto
    if ($endpoint !== 'productos') {
        http_response_code(404);
        echo json_encode(['error' => 'Recurso no encontrado', 'endpoint' => $endpoint]);
        exit;
    }

    // Obtener el cuerpo de la petición para métodos POST y PUT
    $rawInput = file_get_contents('php://input');
    $input = json_decode($rawInput, true);

    // Debug de la petición
    if ($method === 'POST') {
        error_log("Método: " . $method);
        error_log("Raw input: " . $rawInput);
        error_log("Input decodificado: " . print_r($input, true));
    }

    switch ($method) {
        case 'POST':
            // Consultar todos los productos
            $stmt = $pdo->query('SELECT * FROM productos');
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'mensaje' => 'Productos encontrados',
                'total' => count($productos),
                'productos' => $productos
            ]);
            break;

        case 'GET':
            if ($id) {
                // Buscar producto específico
                $stmt = $pdo->prepare('SELECT * FROM productos WHERE id = ?');
                $stmt->execute([$id]);
                $producto = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($producto) {
                    echo json_encode($producto);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Producto no encontrado']);
                }
            } else {
                // Listar todos los productos
                $stmt = $pdo->query('SELECT * FROM productos');
                $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($productos)) {
                    echo json_encode([
                        'mensaje' => 'No hay productos en la base de datos',
                        'total' => 0,
                        'productos' => []
                    ]);
                } else {
                    echo json_encode([
                        'mensaje' => 'Productos encontrados',
                        'total' => count($productos),
                        'productos' => $productos
                    ]);
                }
            }
            break;

        case 'PUT':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Se requiere ID para actualizar']);
                break;
            }
            if (!isset($input['nombre']) || !isset($input['precio'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Se requieren los campos nombre y precio']);
                break;
            }
            
            $stmt = $pdo->prepare('UPDATE productos SET nombre = ?, precio = ? WHERE id = ?');
            $stmt->execute([$input['nombre'], $input['precio'], $id]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['mensaje' => 'Producto actualizado correctamente']);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Producto no encontrado']);
            }
            break;

        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'Se requiere ID para eliminar']);
                break;
            }
            
            $stmt = $pdo->prepare('DELETE FROM productos WHERE id = ?');
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                echo json_encode(['mensaje' => 'Producto eliminado correctamente']);
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

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error de base de datos',
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error del servidor',
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>