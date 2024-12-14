<?php

namespace Alex\Blog\Domain\Services;

use Alex\Blog\Persistence\Entities\Comment;
use Alex\Blog\Persistence\Entities\Post;
use Alex\Blog\Persistence\Entities\User;
use DateTime;

class CommentService
{
    public function getAllComments(): array
    {
        return Comment::getAllComments();
    }

    public function createComment(User $user, Post $post, string $content): ?Comment
    {
        return new Comment(
            id: 0,
            user: $user->getId(),
            post: $post->getId(),
            content: $content,
            created_at: new DateTime(),
        )->save();
    }

    public function updateComment(Comment $comment, array $data): Comment
    {
        if (isset($data['content'])) {
            $comment->setContent($data['email']);
        }

        return $comment->save();
    }

    public function deleteComment(int $id): bool
    {
        return Comment::deleteComment($id);
    }

    public function getCommentById(int $id): ?Comment
    {
        return Comment::getCommentById($id);
    }

    public function getAllCommentsByPost(Post $post): array
    {
        return Comment::getAllByPost($post);
    }
}