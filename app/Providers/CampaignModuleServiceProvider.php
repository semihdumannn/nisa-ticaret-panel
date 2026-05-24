<?php

namespace App\Providers;

use App\Modules\Campaign\Domain\Contracts\CampaignRepositoryInterface;
use App\Modules\Campaign\Domain\Contracts\CouponRepositoryInterface;
use App\Modules\Campaign\Infrastructure\Repositories\EloquentCampaignRepository;
use App\Modules\Campaign\Infrastructure\Repositories\EloquentCouponRepository;
use Illuminate\Support\ServiceProvider;

class CampaignModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CampaignRepositoryInterface::class, EloquentCampaignRepository::class);
        $this->app->bind(CouponRepositoryInterface::class, EloquentCouponRepository::class);
    }
}
