<?php

namespace Alex\Blog\Controllers;

use Alex\Blog\Domain\Services\UserService;
use Alex\Blog\Persistence\Role;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function showRegistrationForm(Request $request, Response $response): Response
    {
        return $this->renderTemplate($response, 'register.twig');
    }

    public function register(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();

            $role = Role::tryFrom($data['role']);
            if (!$role) {
                error_log("Invalid role: " . $data['role']);
                throw new \InvalidArgumentException("Invalid role: {$data['role']}");
            }

            $this->userService->createUser(
                $data['username'],
                $data['email'],
                $data['password'],
                $role
            );

            return $this->renderTemplate($response, 'login.twig')->withStatus(201);
        } catch (\InvalidArgumentException $e) {
            error_log($e->getMessage());
            return $this->renderTemplate($response, 'register.twig')->withStatus(400);
        } catch (\Exception $e) {
            return $this->renderTemplate($response, 'register.twig')->withStatus(500);
        }
    }

    public function showLoginForm(Request $request, Response $response): Response
    {
        return $this->renderTemplate($response, 'login.twig');
    }

    public function login(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();
            $user = $this->userService->login($data['username'], $data['password']);

            if ($user) {
                if (session_status() == PHP_SESSION_NONE) {
                    session_start();
                }

                $_SESSION['user'] = [
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'role' => $user->getRole(),
                ];


                return $this->renderTemplate($response, 'index.twig', [
                    'username' => $user->getUsername(),
                ]);
            }

            return $this->renderTemplate($response, 'login.twig')->withStatus(401);
        } catch (\Exception $e) {
            return $this->renderTemplate($response, 'login.twig')->withStatus(500);
        }
    }

    public function logout(Request $request, Response $response): Response
    {
        session_destroy();
        return $this->renderTemplate($response, 'logout.twig');
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