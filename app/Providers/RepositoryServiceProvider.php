<?php

namespace App\Providers;

use App\Repositories\Member\MemberRepository;
use App\Repositories\Member\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            MemberRepository::class
        );
    }
}