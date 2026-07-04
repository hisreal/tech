<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Core\Application;
use App\Middleware\AuthMiddleware;
use App\Middleware\AuthorizationMiddleware;

$router = Application::instance()->router();

$router->get('/', [HomeController::class, 'index'], ['name' => 'home']);
$router->get('/health', [HomeController::class, 'health'], ['name' => 'health']);

$router->post('/login', static function (): string {
    return 'Login endpoint placeholder. Future authentication service will handle this route.';
}, ['name' => 'login.store']);

$router->group(['prefix' => '/admin', 'middleware' => [AuthMiddleware::class, new AuthorizationMiddleware(['super admin', 'admin'])]], static function ($router): void {
    $router->get('/dashboard', static fn (): string => 'Admin dashboard backend route placeholder.', ['name' => 'admin.dashboard']);
});

$router->group(['prefix' => '/teacher', 'middleware' => [AuthMiddleware::class, new AuthorizationMiddleware(['teacher'])]], static function ($router): void {
    $router->get('/profile/{id}', static fn ($request, string $id): string => 'Teacher profile backend route placeholder for ID ' . $id, ['name' => 'teacher.profile']);
});

$router->group(['prefix' => '/accountant', 'middleware' => [AuthMiddleware::class, new AuthorizationMiddleware(['accountant'])]], static function ($router): void {
    $router->get('/finance', static fn (): string => 'Accountant finance backend route placeholder.', ['name' => 'accountant.finance']);
});

$router->group(['prefix' => '/student', 'middleware' => [AuthMiddleware::class, new AuthorizationMiddleware(['student'])]], static function ($router): void {
    $router->get('/portal', static fn (): string => 'Student portal backend route placeholder.', ['name' => 'student.portal']);
});
