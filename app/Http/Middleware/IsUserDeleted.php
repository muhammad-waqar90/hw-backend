<?php

namespace App\Http\Middleware;

use App\DataObject\RoleData;
use App\Repositories\IU\IuUserRepository;
use Closure;
use Illuminate\Http\Request;

class IsUserDeleted
{
    /**
     * @var iuUserRepository
     */
    private $iuUserRepository;

    /**
     * Handle an incoming request.
     *
     * @param IuUserRepository $iuUserRepository
     */

    public function __construct(IuUserRepository $iuUserRepository)
    {
        $this->iuUserRepository = $iuUserRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $this->iuUserRepository->getUser((int) $request->id, false, RoleData::INDEPENDENT_USER, true);
        if($user->trashed() || $user->restoreUser)
            return response()->json(['errors' => 'Forbidden: request of a deleted user'], 403);

        return $next($request);
    }
}
