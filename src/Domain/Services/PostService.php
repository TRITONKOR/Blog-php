<?php

namespace Alex\Blog\Domain\Services;

use Alex\Blog\Persistence\Entities\Post;
use Alex\Blog\Persistence\Entities\Tag;
use Alex\Blog\Persistence\Entities\User;
use DateTime;

class PostService
{
    public function getAllPosts(): array
    {
        return Post::getAllPosts();
    }

    public function createPost(User $user, array $tags, array $comments, $title, string $content): ?Post
    {
        $tagObjects = [];
        foreach ($tags as $tagName) {
            $tag = Tag::getTagByName($tagName);
            if (!$tag) {

                $tag = new Tag(
                    id: 0,
                    name: $tagName);
                $tag->save();
            }
            $tagObjects[] = $tag;
        }

        return new Post(
            id: 0,
            user: $user,
            title: $title,
            content: $content,
            tags: $tagObjects,
            comments: $comments,
            created_at: new DateTime(),
            updated_at: new DateTime()
        )->save();
    }

    public function updatePost(Post $post, array $data): Post
    {
        if (isset($data['title'])) {
            $post->setTitle($data['title']);
        }
        if (isset($data['content'])) {
            $post->setContent($data['content']);
        }
        $post->setUpdatedAt(new DateTime());

        return $post->save();
    }

    public function deletePost(int $id): bool
    {
        return Post::deletePost($id);
    }

    public function getPostById(int $id): ?Post
    {
        return Post::getPostById($id);
    }

    public function getPostByTitle(string $title): ?Post
    {
        return Post::getByTitle($title);
    }

    public function getAllTags(): array
    {
        return Tag::getAllTags();
    }

}