<?php

declare(strict_types=1);

namespace Alex\Blog\Persistence;

enum Role: string
{
    case BLOGGER = 'Blogger';
    case VIEWER = 'Viewer';

    public static function fromString(string $role): ?self
    {
        return match ($role) {
            'Blogger' => self::BLOGGER,
            'Viewer' => self::VIEWER,
            default => null,
        };
    }
}