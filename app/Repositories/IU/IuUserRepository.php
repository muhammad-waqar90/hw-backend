<?php

namespace App\Repositories\IU;

use App\DataObject\RoleData;
use App\DataObject\UserProgressData;
use App\Models\RestoreUser;
use App\Models\User;
use App\Models\UserFeedback;
use App\Models\UserProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class IuUserRepository
{
    private User $user;
    private UserProfile $userProfile;
    private UserFeedback $userFeedback;
    private RestoreUser $restoreUser;

    public function __construct(User $user, UserProfile $userProfile, UserFeedback $userFeedback, RestoreUser $restoreUser)
    {
        $this->user = $user;
        $this->userProfile = $userProfile;
        $this->userFeedback = $userFeedback;
        $this->restoreUser = $restoreUser;
    }

    /**
     * @param $name
     * @param $email
     * @param $password
     * @param bool $isMinor
     * @return User
     */
    public function create(
        $name,
        $firstName,
        $lastName,
        $password,
        bool $communicationAccepted,
        bool $isMinor = false
    ) {
        return $this->user->create([
            'name'            => $name,
            'first_name'      => $firstName,
            'last_name'       => $lastName,
            'password'        => bcrypt($password),
            'is_subscribed'   => $communicationAccepted,
            'age_verified_at' => $isMinor ? null : Carbon::now()
        ]);
    }

    public function createUserProfile($userId, $email, $dateOfBirth = null)
    {
        return $this->userProfile->create([
            'user_id'       => $userId,
            'email'         => $email,
            'date_of_birth' => $dateOfBirth
        ]);
    }

    public function findById($id)
    {
        return $this->user
            ->where('id', $id)
            ->first();
    }

    /**
     * get detail of a particular user.
     * @param $role
     * @param $id
     * @param bool $userProfile
     * @return mixed
     */
    public function getUser($id, bool $userProfile = false, $role = RoleData::INDEPENDENT_USER, $trashed = false)
    {
        return $this->user
            ->where('id', $id)
            ->where('role_id', $role)
            ->when($userProfile, function ($query) {
                return $query->with('userProfile');
            })
            ->when($trashed, function ($query) {
                return $query
                    ->with('restoreUser', function ($q) {
                        $q->addSelect('user_id');
                    })
                    ->withTrashed();
            })
            ->first();
    }

    public function findByName($name)
    {
        return $this->user
            ->where('name', $name)
            ->first();
    }

    public static function iuUserOwnsCourse($userId, $courseId)
    {
        $userOwnsCourse = DB::table('course_user')->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->count();
        return $userOwnsCourse ? true : false;
    }

    public static function courseIdsOwnedByUser($userId)
    {
        return DB::table('course_user')->select('course_id')
            ->where('user_id', $userId)
            ->get()
            ->pluck(['course_id']);
    }

    public function updateUserLastActive($userId)
    {
        return $this->user
            ->where('id', $userId)
            ->update([
                'last_active' => Carbon::now()->toDateTimeString()
            ]);
    }

    public function getUserOverview($userId)
    {
        $ongoingCoursesCount = DB::table('user_progress')
            ->selectRaw("'ongoingCoursesCount' as 'name', count(user_progress.id) as count")
            ->join('course_user as cu', function ($query) {
                $query->on('cu.course_id', '=', 'user_progress.entity_id')
                    ->on('cu.user_id', 'user_progress.user_id');
            })
            ->where('user_progress.progress', '<', UserProgressData::COMPLETED_PROGRESS)
            ->where('user_progress.entity_type', UserProgressData::ENTITY_COURSE)
            ->where('user_progress.user_id', $userId);

        $completedCoursesCount = DB::table('user_progress')
            ->selectRaw("'completedCoursesCount' as 'name',count(user_progress.id) as count")
            ->join('course_user as cu', function ($query) {
                $query->on('cu.course_id', '=', 'user_progress.entity_id')
                    ->on('cu.user_id', 'user_progress.user_id');
            })
            ->where('user_progress.progress', '=', UserProgressData::COMPLETED_PROGRESS)
            ->where('user_progress.entity_type', UserProgressData::ENTITY_COURSE)
            ->where('user_progress.user_id', $userId);

        return DB::table('certificates')
            ->selectRaw("'certificatesCount' as 'name', count(certificates.id) as count")
            ->where('certificates.user_id', $userId)
            ->unionAll($ongoingCoursesCount)
            ->unionAll($completedCoursesCount)
            ->get();
    }

    public function createRestoreUser($userId)
    {
        return $this->restoreUser->create([
            'user_id' => $userId,
            'token' => Str::random(40)
        ]);
    }

    public function deleteRestoreUser($token)
    {
        $restoreUser = $this->restoreUser->where('token', $token)->first();
        if (!$restoreUser)
            throw new \Exception();

        $restoreUser->delete();
        return $restoreUser;
    }

    public function deleteUser($user)
    {
        return $user->trashed() ? $user->forceDelete() : $user->delete();
    }

    public function spoofEmail($userId, $email)
    {
        $spoofEmail = $userId . '_' . $email;
        return $this->userProfile
            ->where('user_id', $userId)
            ->update([
                'email' => $spoofEmail,
            ]);
    }

    public function enableUser($user)
    {
        return $user->update([
            'is_enabled' => 1
        ]);
    }

    public function disableUser($user)
    {
        return $user->update([
            'is_enabled' => 0
        ]);
    }

    public function createUserFeedback($userId, $feedback, $type)
    {
        return $this->userFeedback->create([
            'user_id'  => $userId,
            'feedback' => $feedback,
            'type'     => $type
        ]);
    }

    public function onExpiredRestoreUser($userId, $email, $token)
    {
        $this->spoofEmail($userId, $email);
        $user = $this->findById($userId);
        $this->deleteUser($user);
        $this->deleteRestoreUser($token);
        return;
    }
}
