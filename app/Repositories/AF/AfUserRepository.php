<?php

namespace App\Repositories\AF;

use App\DataObject\ActivityStatusData;
use App\DataObject\RoleData;
use App\Models\User;
use Carbon\Carbon;

class AfUserRepository
{
    private User $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function getUserList($role, $searchText = null, $activeStatus = null, $courseId = null)
    {
        return $this->getUserListQuery($role, $searchText, $activeStatus, $courseId)
            ->with('restoreUser', function ($q) {
                $q->addSelect('user_id');
            })
            ->withTrashed()
            ->paginate(20)
            ->appends([
                'searchText'    => $searchText,
                'activeStatus'  => $activeStatus,
                'courseId'      => $courseId,
            ]);
    }

    public function getUserListQuery($role, $searchText = null, $activeStatus = null, $courseId = null)
    {
        return $this->model
            ->select('users.id', 'users.name', 'users.first_name', 'users.last_name', 'users.is_enabled', 'users.last_active', 'users.deleted_at')
            ->where('role_id', $role)
            ->when($searchText, function ($query, $searchText) {
                return $query->where(function ($query) use ($searchText) {
                    $query->where('users.name', 'LIKE', "%$searchText%")
                        ->orWhere('users.first_name', 'LIKE', "%$searchText%")
                        ->orWhere('users.last_name', 'LIKE', "%$searchText%");
                });
            })
            ->when($activeStatus, function ($query, $activeStatus) {
                return $query->where(function ($query) use ($activeStatus) {
                    $this->lastActiveQuery($query, $activeStatus);
                });
            })
            ->when($courseId, function ($query, $courseId) {
                return $query->where(function ($query) use ($courseId) {
                    $query->whereHas('enrolledCourses', function ($query) use ($courseId) {
                        $query->where('courses.id', $courseId);
                    });
                });
            })
            ->when($role === RoleData::INDEPENDENT_USER, function ($query) {
                return $query->withCount('enrolledCourses');
            })
            ->when($role === RoleData::ADMIN, function ($query) {
                return $query->withCount('permGroups');
            })
            ->orderBy('users.name', 'ASC');
    }

    /**
     * generate filter wrt activeStatus
     * @param $query
     * @param $activeStatus
     * @return mixed
     */
    public function lastActiveQuery($query, $activeStatus)
    {
        $filterLastActive = ActivityStatusData::FILTER_FROM_TO[$activeStatus];
        $from = Carbon::now()->subDays($filterLastActive['from']);
        $to = Carbon::now()->subDays($filterLastActive['to']);

        if ((int)$activeStatus === 5)
            return $query->where('last_active', '<=', $to);

        return $query->where('last_active', '>=', $from)->where('last_active', '<=', $to);
    }
}
