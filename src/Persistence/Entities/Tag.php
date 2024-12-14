<?php

namespace Alex\Blog\Persistence\Entities;

use PDO;

class Tag extends Entity
{
    public function __construct(
        private int $id,
        private string $name,
    )
    {
        if (!isset(self::$db)) {
            self::getDb();
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function save(): ?self
    {
        if ($this->id) {
            $stmt = self::getDb()->prepare("UPDATE tags SET name = :name WHERE id = :id");
            $success = $stmt->execute(['name' => $this->name, 'id' => $this->id]);

        } else {
            $stmt = self::getDb()->prepare("INSERT INTO tags (name) VALUES (:name)");
            $success = $stmt->execute([
                'name' => $this->name]);
            if ($success) {
                $this->id = (int)self::getDb()->lastInsertId();
            }
        }

        return $success ? $this : null;
    }

    public static function getAllTags(): array
    {
        return self::getAll('tags', fn(array $data) => self::rowMap($data));
    }

    public static function getTagByName(string $title): ?self
    {
        $stmt = self::getDb()->prepare("SELECT * FROM tags WHERE name = :name");
        $stmt->execute(['name' => $title]);
        $postData = $stmt->fetch(PDO::FETCH_ASSOC);

        return $postData ? self::rowMap($postData) : null;
    }

    public static function rowMap(array $data): self
    {
        return new self(
            id: (int)$data['id'],
            name: $data['name']
        );
    }
}