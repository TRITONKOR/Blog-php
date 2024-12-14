<?php

namespace Alex\Blog\Persistence\Entities;

use DateTime;
use PDO;

class Comment extends Entity
{
    public function __construct(
        private int $id,
        private int $user,
        private int $post,
        private string $content,
        private DateTime $created_at
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

    public function getUser(): int
    {
        return $this->user;
    }

    public function setUser(int $user): void
    {
        $this->user = $user;
    }

    public function getPost(): int
    {
        return $this->post;
    }

    public function setPost(int $post): void
    {
        $this->post = $post;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->created_at;
    }

    public function setCreatedAt(DateTime $created_at): void
    {
        $this->created_at = $created_at;
    }

    public function save(): ?self
    {
        if ($this->id) {
            $stmt = self::getDb()->prepare("UPDATE comments SET content = :content WHERE id = :id");
            $success = $stmt->execute([
                'content' => $this->content,
                'id' => $this->id]);

        } else {
            $stmt = self::getDb()->prepare("INSERT INTO comments (user_id, post_id, content, created_at) VALUES (:user_id, :post_id, :content, :created_at)");
            $success = $stmt->execute([
                'user_id' => $this->user,
                'post_id' => $this->post,
                'content' => $this->content,
                'created_at' => $this->created_at->format('Y-m-d H:i')]);
            if ($success) {
                $this->id = (int)self::getDb()->lastInsertId();
            }
        }

        return $success ? $this : null;
    }

    public static function deleteComment(int $id): bool
    {
        return self::delete('comments', $id);
    }

    public static function getCommentById(int $id): ?self
    {
        return self::getById('comments', $id, fn(array $data) => self::rowMap($data));
    }

    public static function getAllComments(): array
    {
        return self::getAll('comments', fn(array $data) => self::rowMap($data));
    }

    public static function getAllByPostId(int $postId): array
    {
        $stmt = self::getDb()->prepare("SELECT * FROM comments WHERE post_id = :post_id");
        $stmt->execute(['post_id' => $postId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => self::rowMap($row), $rows);
    }

    private static function parseDate(?string $dateString): DateTime
    {
        try {
            return new DateTime($dateString);
        } catch (DateMalformedStringException $e) {
            error_log("Invalid date format: " . $e->getMessage());
            return new DateTime();
        }
    }

    private static function rowMap(array $data): self
    {
        $createdAt = self::parseDate($data['createdAt'] ?? null);

        return new self(
            id: (int)$data['id'],
            user: (int)$data['user_id'],
            post: (int)$data['post_id'],
            content: $data['content'],
            created_at: $createdAt,
        );
    }
}