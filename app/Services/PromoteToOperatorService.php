<?php

namespace App\Services;

use App\DTOs\PromoteToOperator;
use App\Models\User;
use Illuminate\Validation\UnauthorizedException;

class PromoteToOperatorService
{
    public function execute(PromoteToOperator $promoteToOperator): void
    {
        $actor = User::findOrFail($promoteToOperator->actorId);

        if (! $actor->isAdmin()) {
            throw new UnauthorizedException;
        }
        $userId = $promoteToOperator->userId;
        $user = User::findOrFail($userId);
        $user->makeOperator();
        $user->save();
    }
}
