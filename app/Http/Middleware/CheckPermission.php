<?php

namespace App\Http\Middleware;

use App\Repositories\HA\PermissionRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class CheckPermission
{
    /**
     * @var PermissionRepository
     */
    private $permissionRepository;

    /**
     * Handle an incoming request.
     */
    public function __construct(PermissionRepository $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    public function handle(Request $request, Closure $next, $permission)
    {
        $canAccess = $this->permissionRepository->hasUserPermissionIds($request->user()->id, $permission);
        if (! $canAccess) {
            return response()->json(['errors' => Lang::get('auth.forbidden')], 403);
        }

        return $next($request);
    }
}
