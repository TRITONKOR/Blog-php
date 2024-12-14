<?php

declare(strict_types=1);

namespace Alex\Blog\Persistence\Entities;

use PDO;
use Alex\Blog\Persistence\Database;

abstract class Entity
{
    protected static PDO $db;

    protected static function getDb(): PDO
    {
        if (!isset(self::$db)) {
            self::$db = Database::getInstance();
        }
        return self::$db;
    }

    public static function getAll(string $table, callable $rowMapper): array
    {
        $stmt = self::getDb()->prepare("SELECT * FROM {$table}");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map($rowMapper, $data);
    }

    public static function delete(string $table, int $id): bool
    {
        $stmt = self::getDb()->prepare("DELETE FROM {$table} WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public static function getById(string $table, int $id, callable $rowMapper): ?static
    {
        $stmt = self::getDb()->prepare("SELECT * FROM {$table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $rowMapper($data) : null;
    }
}
