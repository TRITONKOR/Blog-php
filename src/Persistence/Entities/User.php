<?php

declare(strict_types=1);

namespace Alex\Blog\Persistence\Entities;

use Alex\Blog\Persistence\Role;
use PDO;


class User extends Entity
{
    public function __construct(
        private int $id,
        private string $username,
        private string $email,
        private string $password,
        private Role $role,
    )
    {
        if (!isset(self::$db)) {
            self::getDb();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function setRole(Role $role): void
    {
        $this->role = $role;
    }

    public function save(): ?self
    {
        if ($this->id) {
            $stmt = self::getDb()->prepare("UPDATE users SET username = :username, email = :email, role = :role WHERE id = :id");
            $success = $stmt->execute(['username' => $this->username, 'email' => $this->email, 'role' => $this->role->value, 'id' => $this->id]);

        } else {
            $stmt = self::getDb()->prepare("INSERT INTO users (username, email, password, role) VALUES (:username, :email, :password, :role)");
            $success = $stmt->execute(['username' => $this->username, 'email' => $this->email, 'password' => $this->password, 'role' => $this->role->value]);
            if ($success) {
                $this->id = (int) self::getDb()->lastInsertId();
            }
        }

        return $success ? $this : null;
    }

    public static function deleteUser(int $id): bool
    {
        return self::delete('users', $id);
    }

    public static function getUserById(int $id): ?self
    {
        return self::getById('users', $id, fn(array $data) => self::rowMap($data));
    }

    public static function getByUsername(string $username): ?self
    {
        $stmt = self::getDb()->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        return $userData ? self::rowMap($userData) : null;
    }

    public static function getAllUsers(): array
    {
        return self::getAll('users', fn(array $data) => self::rowMap($data));
    }

    private static function rowMap(array $data): self
    {
        return new self(
            id: (int)$data['id'],
            username: $data['username'],
            email: $data['email'],
            password: $data['password'],
            role: Role::fromString($data['role']),
        );
    }
}