<?php

declare(strict_types=1);

namespace Alex\Blog\Domain\Services;

use Alex\Blog\Persistence\Entities\User;
use Alex\Blog\Persistence\Role;
class UserService
{
    public function getAllUsers(): array
    {
        return User::getAllUsers();
    }

    public function createUser(string $username, string $email, string $password, Role $role): ?User
    {
        return new User(
            id: 0,
            username: $username,
            email: $email,
            password: password_hash($password, PASSWORD_DEFAULT),
            role: $role
        )->save();
    }

    public function login(string $username, string $password): ?User
    {
        $user = User::getByUsername($username);

        if ($user && password_verify($password, $user->getPassword())) {
            error_log("Password verification successful for user: " . $username);
            return $user;
        } else {
            error_log("Password verification failed for user: " . $username);
        }

        return null;
    }

    public function updateUser(User $user, array $data): User
    {
        if (isset($data['username'])) {
            $user->setUsername($data['username']);
        }
        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['password'])) {
            $user->setPassword($data['password']);
        }
        if (isset($data['role'])) {
            $user->setRole($data['tole']);
        }

        return $user->save();
    }

    public function deleteUser(int $id): bool
    {
        return User::deleteUser($id);
    }

    public function getUserById(int $id): ?User
    {
        return User::getUserById($id);
    }

    public function getUserByUsername(string $username): ?User
    {
        return User::getByUsername($username);
    }
}