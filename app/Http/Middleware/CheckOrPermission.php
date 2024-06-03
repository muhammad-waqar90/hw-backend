<?php

namespace App\Http\Middleware;

use App\Repositories\HA\PermissionRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class CheckOrPermission
{
    private PermissionRepository $permissionRepository;

    /**
     * Handle an incoming request.
     */
    public function __construct(PermissionRepository $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    public function handle(Request $request, Closure $next, ...$permissions)
    {
        $userPermissions = $this->permissionRepository->getUserPermissionIds($request->user()->id)->toArray();
        if (! $this->canAccess($permissions, $userPermissions)) {
            return response()->json(['errors' => Lang::get('auth.forbidden')], 403);
        }

        return $next($request);
    }

    public function canAccess($permissions, $userPermissions)
    {
        return (count(array_intersect($permissions, $userPermissions))) ? true : false;
    }
}
