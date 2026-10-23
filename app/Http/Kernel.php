<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \App\Http\Middleware\SetCacheHeaders::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\AutoLoginFromCookie::class,
            \App\Http\Middleware\ContentSecurityPolicy::class,
            \App\Http\Middleware\UpdateLastSeen::class,
        ],

        'api' => [
           // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\UpdateLastSeen::class,
        ],
    ];

    /**
     * The application's middleware aliases.
     *
     * Aliases may be used instead of class names to conveniently assign middleware to routes and groups.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'adminsession' => \App\Http\Middleware\CheckAdminSession::class,
        'usersession' => \App\Http\Middleware\CheckUserSession::class,
        'profile.complete' => \App\Http\Middleware\RequireProfileComplete::class,
        'shipsession' => \App\Http\Middleware\CheckShipSession::class,
        'adminrole' => \App\Http\Middleware\AdminRole::class,
        'admin.permission' => \App\Http\Middleware\CheckAdminPermission::class,
        'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,

        // ✅ API Documentation Authentication
        'docs.auth' => \App\Http\Middleware\DocsAuthentication::class,
        'user.not_disabled' => \App\Http\Middleware\EnsureUserNotDisabled::class,
        'user.phone_complete' => \App\Http\Middleware\EnsurePhoneComplete::class,
        'admin.2fa' => \App\Http\Middleware\Admin2FAVerified::class,
        'lowercase.url' => \App\Http\Middleware\LowercaseLocationUrl::class,
    ];

    /**
     * @deprecated Use $middlewareAliases instead
     */
    protected $routeMiddleware = [
        // This is deprecated in Laravel 11+
        // Keeping for backwards compatibility
        'docs.auth' => \App\Http\Middleware\DocsAuthentication::class,
    ];
}
