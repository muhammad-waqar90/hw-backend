<?php

namespace App\Repositories;

use App\Models\PasswordHistory;
use Illuminate\Support\Facades\Hash;

class PasswordHistoryRepository
{
    private PasswordHistory $passwordHistory;

    public function __construct(PasswordHistory $passwordHistory)
    {
        $this->passwordHistory = $passwordHistory;
    }

    public function storeCurrentPassword($userId, $password)
    {
        return $this->passwordHistory->create([
            'user_id' => $userId,
            'password' => bcrypt($password),
        ]);
    }

    public function getPreviousPasswordsOfUser($userId, $depth = null)
    {
        return $this->passwordHistory
            ->where('user_id', $userId)
            ->latest()
            ->when($depth, function ($query) use ($depth) {
                $query->take($depth);
            })
            ->get();
    }

    public function deletePasswordsFromHistory($userId, $keepHistories)
    {
        $keepHistoriesIds = $keepHistories->pluck('id')->all();

        $this->passwordHistory
            ->where('user_id', $userId)
            ->whereNotIn('id', $keepHistoriesIds)
            ->delete();
    }

    public function managePasswordHistoryDepth($userId, $depth)
    {
        $passwordHistories = self::getPreviousPasswordsOfUser($userId);

        if ($passwordHistories->count() > $depth) {
            $keepHistories = $passwordHistories->take($depth);
            self::deletePasswordsFromHistory($userId, $keepHistories);
        }
    }

    public static function isFromPasswordHistory($userId, $newPassword)
    {
        $passwordHistories = PasswordHistory::where('user_id', $userId)->get();

        foreach ($passwordHistories as $passwordHistory) {
            if (Hash::check($newPassword, $passwordHistory->password)) {
                return true;
                break;
            }
        }

        return false;
    }
}
