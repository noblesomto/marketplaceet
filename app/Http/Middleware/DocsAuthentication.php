<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API Documentation Authentication Middleware
 *
 * Protects API documentation with simple token-based or basic authentication
 * Can be configured via .env file
 */
class DocsAuthentication
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip authentication in local environment (optional)
        if (app()->environment('local') && config('scribe.auth.skip_local', false)) {
            return $next($request);
        }

        $authMethod = config('scribe.auth.method', 'token'); // 'token' or 'basic'

        if ($authMethod === 'basic') {
            return $this->handleBasicAuth($request, $next);
        }

        return $this->handleTokenAuth($request, $next);
    }

    /**
     * Handle HTTP Basic Authentication
     */
    protected function handleBasicAuth(Request $request, Closure $next): Response
    {
        $users = $this->getAuthorizedUsers();

        // Check if Authorization header exists
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Basic ')) {
            return $this->unauthorizedResponse();
        }

        // Decode credentials
        $credentials = base64_decode(substr($authHeader, 6));
        [$username, $password] = explode(':', $credentials, 2);

        // Verify credentials
        if (isset($users[$username]) && hash_equals($users[$username], $password)) {
            return $next($request);
        }

        return $this->unauthorizedResponse();
    }

    /**
     * Handle Token Authentication
     */
    protected function handleTokenAuth(Request $request, Closure $next): Response
    {
        $validToken = config('scribe.auth.token');

        if (empty($validToken)) {
            return $next($request);
        }

        $providedToken = $request->query('token')
                      ?? $request->header('X-Docs-Token')
                      ?? $request->bearerToken();

        if ($providedToken && hash_equals($validToken, $providedToken)) {
            // ✅ Check if session is available before using it
            if ($request->hasSession()) {
                session(['docs_authenticated' => true]);
            }
            return $next($request);
        }

        // ✅ Check if session is available
        if ($request->hasSession() && session('docs_authenticated')) {
            return $next($request);
        }

        return $this->unauthorizedResponse();
    }

    /**
     * Get authorized users from config
     */
    protected function getAuthorizedUsers(): array
    {
        $users = [];
        $usersConfig = config('scribe.auth.users', '');

        // Format: username1:password1,username2:password2
        foreach (explode(',', $usersConfig) as $user) {
            $user = trim($user);
            if (str_contains($user, ':')) {
                [$username, $password] = explode(':', $user, 2);
                $users[trim($username)] = trim($password);
            }
        }

        return $users;
    }

    /**
     * Return unauthorized response
     */
    protected function unauthorizedResponse(): Response
    {
        if (config('scribe.auth.method') === 'basic') {
            return response('Unauthorized', 401)
                ->header('WWW-Authenticate', 'Basic realm="API Documentation"');
        }

        // For token auth, redirect to login page or show error
        return response()->view('docs-login', [
            'message' => 'Please provide a valid access token',
            'docs_url' => config('scribe.laravel.docs_url', '/api/docs'),
        ], 401);
    }
}
