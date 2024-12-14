<?php

namespace Alex\Blog\Controllers;

use Alex\Blog\Domain\Services\PostService;
use Alex\Blog\Persistence\Entities\User;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class PostController
{
    private PostService $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    public function index(Request $request, Response $response): Response
    {
        try {
            $posts = $this->postService->getAllPosts();
            return $this->renderTemplate($response, "index.twig", ['posts' => $posts]);
        } catch (\Exception $e) {
            return $this->respondError($response, $e->getMessage());
        }
    }

    public function create(Request $request, Response $response): Response
    {
        try {
            $tags = $this->postService->getAllTags();

            return $this->renderTemplate($response, 'create-post.twig', ['tags' => $tags]);
        } catch (\Exception $e) {
            return $this->respondError($response, $e->getMessage());
        }
    }

    public function store(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            $userId = $_SESSION['user']['id'] ?? null;
            if (!$userId) {
                return $this->respondNotFound($response, "User with ID not found.");
            }

            $user = User::getUserById($userId);
            if (!$user) {
                return $this->respondNotFound($response, "User with ID {$userId} not found.");
            }

            $post = $this->postService->createPost(
                user: $user,
                tags: $data['tags'] ?? [],
                comments: $data['comments'] ?? [],
                title: $data['title'],
                content: $data['content']
            );

            return $this->respondJson($response, $post, 201);
        } catch (\Exception $e) {
            return $this->respondError($response, $e->getMessage());
        }
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int)$args['id'];
            $post = $this->postService->getPostById($id);
            if (!$post) {
                return $this->respondNotFound($response, "Post with ID $id not found.");
            }

            return $this->renderTemplate($response, 'show.twig', ['post' => $post]);
        } catch (\Exception $e) {
            return $this->respondError($response, $e->getMessage());
        }
    }

    public function edit(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int)$args['id'];
            $post = $this->postService->getPostById($id);
            if (!$post) {
                return $this->respondNotFound($response, "Post with ID $id not found.");
            }

            return $this->renderTemplate($response, 'edit-post.twig', ['post' => $post]);
        } catch (\Exception $e) {
            return $this->respondError($response, $e->getMessage());
        }
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int)$args['id'];
            $post = $this->postService->getPostById($id);
            if (!$post) {
                return $this->respondNotFound($response, "Post with ID $id not found.");
            }

            $data = $request->getParsedBody();
            $updatedPost = $this->postService->updatePost($post, $data);

            return $this->renderTemplate($response, 'show.twig', ['post' => $updatedPost]);
        } catch (\Exception $e) {
            return $this->respondError($response, $e->getMessage());
        }
    }

    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int)$args['id'];
            $success = $this->postService->deletePost($id);
            $posts = $this->postService->getAllPosts();
            if ($success) {
                return $this->renderTemplate($response, "index.twig", ['posts' => $posts]);
            }
            return $this->respondError($response, "Failed to delete post with ID $id.", 500);
        } catch (\Exception $e) {
            return $this->respondError($response, $e->getMessage());
        }
    }

    private function respondJson(Response $response, mixed $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    private function respondError(Response $response, string $message, int $status = 500): Response
    {
        return $this->respondJson($response, ['error' => $message], $status);
    }

    private function respondNotFound(Response $response, string $message): Response
    {
        return $this->respondError($response, $message, 404);
    }

    private function renderTemplate(Response $response, string $template, array $data = []): Response
    {
        global $twig;

        try {
            // Передача шляху до шаблонів через Twig
            $html = $twig->render($template, $data);
            $response->getBody()->write($html);
            return $response->withHeader('Content-Type', 'text/html');
        } catch (\Twig\Error\LoaderError|\Twig\Error\RuntimeError|\Twig\Error\SyntaxError $e) {
            $response->getBody()->write("Error rendering template: " . $e->getMessage());
            return $response->withStatus(500);
        }
    }
}