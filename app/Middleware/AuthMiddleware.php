<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Core\Application;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;

/**
 * Verifies authenticated sessions, remember-me state, and session timeout.
 */
final class AuthMiddleware
{
    /**
     * Redirects guests and refreshes active session security metadata.
     */
    public function handle(Request $request): ?Response
    {
        $user = Session::get('user');
        $expiresAt = (int) Session::get('expires_at', 0);

        if (!$user && Session::get('remember_user')) {
            Session::set('user', Session::get('remember_user'));
            $user = Session::get('user');
        }

        if (!$user) {
            Session::flash('warning', 'Please sign in to continue.');
            return Response::redirect(Application::instance()->config('app.url') . '/login.php');
        }

        if ($expiresAt > 0 && time() > $expiresAt) {
            Session::destroy();
            return Response::redirect(Application::instance()->config('app.url') . '/login.php');
        }

        $lifetime = (int) Application::instance()->config('session.lifetime', 120);
        Session::set('expires_at', time() + ($lifetime * 60));
        Session::regenerate();

        return null;
    }
}
