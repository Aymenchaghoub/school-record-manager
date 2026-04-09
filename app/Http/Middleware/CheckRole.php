<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Ensure the authenticated user has one of the allowed roles.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return $this->unauthenticatedResponse($request);
        }

        if (! $user->is_active) {
            auth()->logout();

            return $this->forbiddenResponse($request, 'Your account has been deactivated.');
        }

        if (! in_array($user->role, $roles, true)) {
            return $this->forbiddenResponse($request, 'You are not authorized to access this resource.');
        }

        return $next($request);
    }

    private function unauthenticatedResponse(Request $request): Response
    {
        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        return redirect()->route('login');
    }

    private function forbiddenResponse(Request $request, string $message): Response
    {
        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $message,
            ], 403);
        }

        abort(403, $message);
    }

    private function isApiRequest(Request $request): bool
    {
        return $request->expectsJson() || $request->is('api/*');
    }
}
