<?php

namespace App\Services;

use App\DTOs\PromoteToOperator;
use App\Models\User;
class PromoteToOperatorService
{
    public function execute(PromoteToOperator $promoteToOperator)
    {
        $userId = $promoteToOperator->userId;
        $user = User::findOrFail($userId);
        $user->role = 'operator';
        $user->save();
    }
}
