<?php

use Alex\Blog\Domain\Services\CommentService;
use Alex\Blog\Domain\Services\PostService;
use Alex\Blog\Domain\Services\UserService;
use Slim\App;
use Alex\Blog\Controllers\PostController;
use Alex\Blog\Controllers\CommentController;
use Alex\Blog\Controllers\AuthController;

return function (App $app) {
    $postService = new PostService();
    $userService = new UserService();
    $commentService = new CommentService();

    $postController = new PostController($postService);
    $athController = new AuthController($userService);
    $commentController = new CommentController($commentService, $postService);

    $app->get('/', [$postController, 'index']);
    $app->get('/posts/create', [$postController, 'create']);
    $app->post('/posts', [$postController, 'store']);
    $app->get('/posts/{id}', [$postController, 'show']);
    $app->get('/posts/{id}/edit', [$postController, 'edit']);
    $app->post('/posts/{id}', [$postController, 'update']);
    $app->post('/posts/{id}/delete', [$postController, 'delete']);

    $app->post('/comments', [$commentController, 'store']);
    $app->get('/comments', [$commentController, 'create']);
    $app->post('/comments/{id}/edit', [$commentController, 'update']);
    $app->post('/comments/{id}/delete', [$commentController, 'delete']);

    $app->get('/register', [$athController, 'showRegistrationForm']);
    $app->post('/register', [$athController, 'register']);
    $app->get('/login', [$athController, 'showLoginForm']);
    $app->post('/login', [$athController, 'login']);
    $app->get('/logout', [$athController, 'logout']);
};