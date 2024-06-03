<?php

namespace App\Http\Controllers\HA;

use App\DataObject\RoleData;
use App\Http\Controllers\Controller;
use App\Http\Requests\HA\CreateAdminRequest;
use App\Http\Requests\HA\UpdateAdminRequest;
use App\Mail\AdminAccountCreatedEmail;
use App\Repositories\AF\AfTicketRepository;
use App\Repositories\AF\AfUserRepository;
use App\Repositories\AuthenticationRepository;
use App\Repositories\HA\AdminManipulationRepository;
use App\Repositories\IU\IuUserRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class HaAdminManipulationController extends Controller
{
    private AfUserRepository $afUserRepository;

    private IuUserRepository $iuUserRepository;

    private AuthenticationRepository $authenticationRepository;

    private AdminManipulationRepository $adminManipulationRepository;

    private AfTicketRepository $afTicketRepository;

    public function __construct(
        AfUserRepository $afUserRepository,
        IuUserRepository $iuUserRepository,
        AuthenticationRepository $authenticationRepository,
        AdminManipulationRepository $adminManipulationRepository,
        AfTicketRepository $afTicketRepository
    ) {
        $this->afUserRepository = $afUserRepository;
        $this->iuUserRepository = $iuUserRepository;
        $this->authenticationRepository = $authenticationRepository;
        $this->adminManipulationRepository = $adminManipulationRepository;
        $this->afTicketRepository = $afTicketRepository;
    }

    public function createAdmin(CreateAdminRequest $request)
    {
        DB::beginTransaction();
        try {
            $userName = $this->authenticationRepository->generateUsername($request->first_name, $request->last_name);
            $user = $this->adminManipulationRepository->createAdmin($userName, $request->first_name, $request->last_name, RoleData::ADMIN, $request->permGroupIds);
            $this->adminManipulationRepository->createAdminProfile($user->id, $request->email);
            $passwordReset = $this->authenticationRepository->createPasswordResetToken($userName);

            Mail::to($request->email)->queue(new AdminAccountCreatedEmail($user, $passwordReset->token, $userName));
            DB::commit();

            return response()->json(['message' => 'Successfully created admin'], 200);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Exception: HaAdminManipulationController@createAdmin', [$e->getMessage()]);
            if ($e->getCode() == 23000) {
                return response()->json(['errors' => $e->getMessage()], 400);
            }

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function getAdminList(Request $request)
    {
        $data = $this->afUserRepository->getUserList(RoleData::ADMIN, $request->query('searchText'));

        return response()->json($data, 200);
    }

    public function getAllAdmins(Request $request)
    {
        $data = $this->afUserRepository
            ->getUserListQuery(RoleData::ADMIN, $request->query('searchText'))
            ->with('adminProfile')
            ->get();

        return response()->json($data, 200);
    }

    public function getAdmin($id)
    {
        $admin = $this->adminManipulationRepository->getAdmin($id, RoleData::ADMIN);
        if (! $admin) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        return response()->json($admin, 200);
    }

    public function updateAdmin($id, UpdateAdminRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->adminManipulationRepository->updateAdmin($id, $request->permGroupIds);
            DB::commit();

            return response()->json(['message' => 'Successfully updated admin'], 200);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('Exception: HaAdminManipulationController@updateAdmin', [$e->getMessage()]);
            if ($e->getCode() == 23000) {
                return response()->json(['errors' => $e->getMessage()], 400);
            }

            return response()->json(['errors' => Lang::get('general.pleaseContactSupportWithCode', ['code' => 500])], 500);
        }
    }

    public function deleteAdmin($id)
    {
        $admin = $this->adminManipulationRepository->getAdmin($id, RoleData::ADMIN);
        if (! $admin) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        $this->adminManipulationRepository->deleteAdmin($admin);

        return response()->json(['message' => 'Successfully deleted admin'], 200);
    }

    public function activateAdmin($id)
    {
        $data = $this->adminManipulationRepository->activateAdmin($id);
        if (! $data) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        return response()->json(['message' => 'Successfully activated admin'], 200);
    }

    public function deactivateAdmin($id)
    {
        $data = $this->adminManipulationRepository->deactivateAdmin($id);
        if (! $data) {
            return response()->json(['errors' => Lang::get('general.notFound')], 404);
        }

        $admin = $this->iuUserRepository->findById($id);
        $this->afTicketRepository->unclaimAllTicketsFromAdmin($id, $admin->name);

        return response()->json(['message' => 'Successfully deactivated admin'], 200);
    }
}
