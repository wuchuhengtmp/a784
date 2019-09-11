<?php

namespace App\Transformers;

use App\Models\Members;
use League\Fractal\TransformerAbstract;

class MemberTransformer extends TransformerAbstract
{
    public function transform(Members $member)
    {
        return [
            'id' => $member->id,
            'nickname' => $member->nickname,
            'avatar' =>  $member->avatar->from == 2 ?  $member->avatar->url : env("APP_URL").$member->avatar->url,
            'sign'   => $member->sign,
            'sex'   => $member->sex,
            'age' => $member->age,
            'born' => $member->born,
            'job' =>  $member->job_name,
            'weixin' =>  $member->weixin,
            'phone' => $member->phone,
            'school' => $member->school,
            'department' => $member->department,
            'professional' => $member->professional,
            'education' => $member->education->name,
            'start_school_at' => $member->start_school_at,
            'next_plan' => $member->next_plan,
            'balance' => number_format($member->balance, 2, '.', ''),
            'created_at' => (string) $member->created_at,
            'updated_at' => (string) $member->updated_at,
        ];
    }
}
