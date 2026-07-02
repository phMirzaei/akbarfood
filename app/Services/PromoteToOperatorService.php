<?php

namespace App\Services;

use App\DTOs\PromoteToOperator;
use App\Models\User;
class PromoteToOperatorService
{
    public function execute(PromoteToOperator $promoteToOperator)
    {
        $actor=User::findOrFail($promoteToOperator->actorId);
        if($actor->role!='admin') {
            throw new \DomainException("فقط ادمین اجازه دسترسی دارد.");
        }

        $userId = $promoteToOperator->userId;
        $user = User::findOrFail($userId);
        $user->role = 'operator';
        $user->save();
    }
}
