<?php
    class ProductoDao{
        private PDO $pdo;
        public function __construct(PDO $pdo){
            $this->pdo = $pdo;
        }
        public function todos(): array{
            $stmt = $this->pdo->prepare("SELECT * FROM productos");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        public funcion porId(int $id): ?array{
            $stmt = $this->pdo->prepare("SELECT * FROM productos WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);
            return $producto ?: null;
        }

        public function insertar(array $producto): int{
            $stmt = $this->pdo->prepare("INSERT INTO productos (nombre, precio) VALUES (?, ?)");
            $stmt->bindParam(':nombre', $producto['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(':precio', $producto['precio'], PDO::PARAM_STR);
            $stmt->execute();
            return (int)$this->pdo->lastInsertId();
        }

        public function actualizar(int $id, string $nombre, float $precio): bool{
            $stmt = $this->pdo->prepare("UPDATE productos SET nombre = ?, precio = ? WHERE id = ?");
            return $stmt->execute([$nombre, $precio, $id]);
        }

        public function eliminar(int $id): bool{
            $stmt = $this->pdo->prepare("DELETE FROM productos WHERE id = ?");
            return $stmt->execute([$id]);
        }
    }