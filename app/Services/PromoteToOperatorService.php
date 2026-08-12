<?php

namespace App\Services;

use App\DTOs\PromoteToOperator;
use App\Models\User;

class PromoteToOperatorService
{
    public function execute(PromoteToOperator $promoteToOperator): void
    {
        $userId = $promoteToOperator->userId;
        $user = User::findOrFail($userId);
        $user->makeOperator();
        $user->save();
    }
}
