<?php

namespace Alex\Blog\Controllers;

use Alex\Blog\Domain\Services\CommentService;
use Alex\Blog\Domain\Services\PostService;
use Alex\Blog\Persistence\Entities\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CommentController
{
    private CommentService $commentService;
    private PostService $postService;

    public function __construct(CommentService $commentService, PostService $postService)
    {
        $this->commentService = $commentService;
        $this->postService = $postService;
    }

    public function create(Request $request, Response $response): Response
    {
        try {
            $tags = $this->postService->getAllTags();

            return $this->renderTemplate($response, 'create-comment.twig', ['tags' => $tags]);
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

            $post = $this->postService->getPostById($data['post_id']);

            if (!$post) {
                return $this->respondNotFound($response, "Post not found.");
            }

            $comment = $this->commentService->createComment($user, $post, $data['content']);
            $newPost = $this->postService->getPostById($data['post_id']);
            return $this->renderTemplate($response, 'show.twig', ['post' => $newPost]);
        } catch (\Exception $e) {
            return $this->respondError($response, $e->getMessage());
        }
    }

    public function edit(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int)$args['id'];
            $comment = $this->commentService->getCommentById($id);
            if (!$comment) {
                return $this->respondNotFound($response, "Comment with ID $id not found.");
            }

            return $this->renderTemplate($response, 'edit-post.twig', ['comment' => $comment]);
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

    public function deleteComment(Request $request, Response $response, array $args): Response
    {
        try {
            $id = (int)$args['id'];
            $success = $this->commentService->deleteComment($id);

            if ($success) {
                return $this->respondJson($response, ['message' => "Comment with ID $id deleted successfully."]);
            }

            return $this->respondError($response, "Failed to delete comment with ID $id.");
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