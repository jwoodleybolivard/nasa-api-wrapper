<?php

namespace App\Providers;

use App\Domain\Repositories\DonkiRepositoryInterface;
use App\Models\Repositories\DonkiRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(DonkiRepositoryInterface::class, DonkiRepository::class);
    }

    public function boot()
    {
        //
    }
}
