<?php

namespace Alex\Blog\Persistence\Entities;

use DateTime;
use PDO;

class Post extends Entity
{
    public function __construct(
        private int      $id,
        private User     $user,
        private string   $title,
        private string   $content,
        private array    $tags,
        private array    $comments,
        private DateTime $created_at,
        private DateTime $updated_at,
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

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
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

    public function getUpdatedAt(): DateTime
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(DateTime $updated_at): void
    {
        $this->updated_at = $updated_at;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): void
    {
        $this->tags = $tags;
    }

    public function getComments(): array
    {
        return $this->comments;
    }

    public function setComments(array $comments): void
    {
        $this->comments = $comments;
    }



    public function save(): ?self
    {
        if ($this->id) {
            $stmt = self::getDb()->prepare("UPDATE posts SET title = :title, content = :content, updated_at = :updated_at WHERE id = :id");
            $success = $stmt->execute([
                'title' => $this->title,
                'content' => $this->content,
                'updated_at' => $this->updated_at->format('Y-m-d H:i'),
                'id' => $this->id]);

        } else {
            $stmt = self::getDb()->prepare("INSERT INTO posts (user_id, title, content, created_at, updated_at) VALUES (:user_id, :title, :content, :created_at, :updated_at)");
            $success = $stmt->execute([
                'user_id' => $this->user->getId(),
                'title' => $this->title,
                'content' => $this->content,
                'created_at' => $this->created_at->format('Y-m-d H:i'),
                'updated_at' => $this->updated_at->format('Y-m-d H:i')]);
            if ($success) {
                $this->saveTags();
                $this->id = (int)self::getDb()->lastInsertId();
            }
        }

        return $success ? $this : null;
    }

    private function saveTags(): void
    {
        $post = self::getByTitle($this->title);

        $stmt = self::getDb()->prepare("DELETE FROM post_tags WHERE post_id = :post_id");
        $stmt->execute(['post_id' => $post->getId()]);

        foreach ($this->tags as $tag) {
            $stmt = self::getDb()->prepare("INSERT INTO post_tags (post_id, tag_id) VALUES (:post_id, :tag_id)");
            $stmt->execute([
                'post_id' => $post->getId(),
                'tag_id' => $tag->getId(),
            ]);
        }
    }

    public static function deletePost(int $id): bool
    {
        return self::delete('posts', $id);
    }

    public static function getPostById(int $id): ?self
    {
        return self::getById('posts', $id, fn(array $data) => self::rowMap($data));
    }

    public static function getByTitle(string $title): ?self
    {
        $stmt = self::getDb()->prepare("SELECT * FROM posts WHERE title = :title");
        $stmt->execute(['title' => $title]);
        $postData = $stmt->fetch(PDO::FETCH_ASSOC);

        return $postData ? self::rowMap($postData) : null;
    }

    public static function getAllPosts(): array
    {
        return self::getAll('posts', fn(array $data) => self::rowMap($data));
    }

    private static function rowMap(array $data): self
    {
        $createdAt = self::parseDate($data['createdAt'] ?? null);
        $updatedAt = self::parseDate($data['updatedAt'] ?? null);

        return new self(
            id: (int)$data['id'],
            user: User::getUserById((int)$data['user_id']),
            title: $data['title'],
            content: $data['content'],
            tags: self::getTagsByPostId((int)$data['id']),
            comments: Comment::getAllByPostId((int)$data['id']),
            created_at: $createdAt,
            updated_at: $updatedAt,
        );
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

    private static function getTagsByPostId(int $postId): array
    {
        $stmt = self::getDb()->prepare("
        SELECT tags.* 
        FROM tags 
        INNER JOIN post_tags ON tags.id = post_tags.tag_id 
        WHERE post_tags.post_id = :post_id
    ");
        $stmt->execute(['post_id' => $postId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($row) => Tag::rowMap($row), $rows);
    }
}