<?php

namespace App\Transformers;

use App\Models\Members as Member;
use League\Fractal\TransformerAbstract;

class MemberTransformer extends TransformerAbstract
{
    public function transform(Member $user)
    {
        return [
            'id' => $user->id,
            'created_at' => (string) $user->created_at,
            'updated_at' => (string) $user->updated_at,
        ];
    }
}
