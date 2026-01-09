<?php

namespace App\Http\Middleware;

use Closure;

class CheckRole
{
    public function handle($request, Closure $next, ...$expectedRoles)
    {
        $u = $request->user();
        if (!$u) abort(403);

        // Map role_id -> role name
        $map = [
            1 => 'Admin',
            2 => 'Customer',
        ];
        $current = $map[$u->role_id] ?? null;

        // Không truyền role -> đã đăng nhập là qua
        if (empty($expectedRoles)) {
            return $next($request);
        }

        // Cho phép nhiều role: checkRole:Admin,Customer
        $ok = in_array($current, $expectedRoles, true);
        abort_if(!$ok, 403);

        return $next($request);
    }
}
