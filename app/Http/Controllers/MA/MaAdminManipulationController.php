<?php

namespace App\Http\Controllers\MA;

use App\DataObject\RoleData;
use App\Http\Controllers\Controller;
use App\Repositories\AF\AfUserRepository;
use App\Repositories\HA\AdminManipulationRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;

class MaAdminManipulationController extends Controller
{
    private AfUserRepository $afUserRepository;

    private AdminManipulationRepository $adminManipulationRepository;

    public function __construct(
        AfUserRepository $afUserRepository,
        AdminManipulationRepository $adminManipulationRepository
    ) {
        $this->afUserRepository = $afUserRepository;
        $this->adminManipulationRepository = $adminManipulationRepository;
    }

    public function getHaAdminList(Request $request)
    {
        $data = $this->afUserRepository->getUserList(RoleData::HEAD_ADMIN, $request->query('searchText'));

        return response()->json($data, 200);
    }

    public function deleteHaAdmin($id)
    {
        $count = $this->adminManipulationRepository->getAdminCount(RoleData::HEAD_ADMIN);
        if ($count < 3) {
            return response()->json(['errors' => 'Minimum amount of admins remaining in the system must be 2'], 400);
        }

        $headAdmin = $this->adminManipulationRepository->getAdmin($id, RoleData::HEAD_ADMIN);
        if (! $headAdmin) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        $this->adminManipulationRepository->deleteAdmin($headAdmin);

        return response()->json(['message' => 'Successfully deleted admin'], 200);
    }
}
