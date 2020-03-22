<?php

namespace App\Policies;

use App\Models\CrawlerTask;
use App\Models\Member;
use Illuminate\Auth\Access\HandlesAuthorization;

class CrawlerTaskPolicy
{
    use HandlesAuthorization;

    /*
     * 两个参数，
     * 第一个参数默认为当前登录用户实例，
     * 第二个参数则为要进行授权的用户实例
     * */
    public function update(Member $member, CrawlerTask $crawlerTask)
    {
        return $member->isAuthorOf($crawlerTask);
    }

    public function destroy(Member $member, CrawlerTask $crawlerTask)
    {
        return $member->isAuthorOf($crawlerTask);
    }


}
