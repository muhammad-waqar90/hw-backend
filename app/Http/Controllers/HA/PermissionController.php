<?php

namespace App\Http\Controllers\HA;

use App\Http\Controllers\Controller;
use App\Http\Requests\HA\CreateUpdatePermGroupRequest;
use App\Repositories\HA\PermissionRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;

class PermissionController extends Controller
{
    private PermissionRepository $permissionRepository;

    public function __construct(PermissionRepository $permissionRepository)
    {
        $this->permissionRepository = $permissionRepository;
    }

    public function getPermissionList(Request $request)
    {
        $data = $this->permissionRepository->getPermissionList($request->query('searchText'));

        return response()->json($data, 200);
    }

    public function createPermGroup(CreateUpdatePermGroupRequest $request)
    {
        DB::beginTransaction();
        try {
            $permGroup = $this->permissionRepository->createPermGroup($request->name, $request->description);

            if (! empty($request->users)) {
                $this->permissionRepository->updatePermGroupUsers($permGroup->id, $request->users);
            }
            if (! empty($request->permissions)) {
                $this->permissionRepository->updatePermGroupPermissions($permGroup->id, $request->permissions);
            }

            DB::commit();

            return response()->json(['message' => 'Successfully created a new permission group'], 201);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Exception: PermissionController@createPermGroup', [$e->getMessage()]);
            if ($e->getCode() == 23000) {
                return response()->json(['errors' => $e->getMessage()], 400);
            }

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function getPermGroupList(Request $request)
    {
        $data = $this->permissionRepository->getPermGroupList($request->query('searchText'));

        return response()->json(['data' => $data], 200);
    }

    public function getPermGroup($id)
    {
        $data = $this->permissionRepository->getPermGroup($id);

        if (! $data) {
            return response()->json(['errors' => 'Group not found'], 404);
        }

        return response()->json(['data' => $data], 200);
    }

    public function updatePermGroup($id, CreateUpdatePermGroupRequest $request)
    {
        DB::beginTransaction();
        try {
            $permGroup = $this->permissionRepository->updatePermGroup($id, $request->name, $request->description);

            $this->permissionRepository->updatePermGroupUsers($permGroup->id, $request->users);
            $this->permissionRepository->updatePermGroupPermissions($permGroup->id, $request->permissions);

            DB::commit();

            return response()->json(['message' => 'Successfully updated permission group'], 200);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Exception: PermissionController@updatePermGroup', [$e->getMessage()]);
            if ($e->getCode() == 23000) {
                return response()->json(['errors' => $e->getMessage()], 400);
            }

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function deletePermGroup($id)
    {
        DB::beginTransaction();
        try {
            $this->permissionRepository->deletePermGroup($id);

            DB::commit();

            return response()->json(['message' => 'Successfully deleted permission group'], 200);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Exception: PermissionController@deletePermGroup', [$e->getMessage()]);
            if ($e->getCode() == 23000) {
                return response()->json(['errors' => $e->getMessage()], 400);
            }

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }
}
