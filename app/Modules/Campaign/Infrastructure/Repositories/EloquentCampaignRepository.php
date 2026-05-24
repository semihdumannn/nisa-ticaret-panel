<?php

namespace App\Modules\Campaign\Infrastructure\Repositories;

use App\Models\Campaign;
use App\Modules\Campaign\Domain\Contracts\CampaignRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentCampaignRepository implements CampaignRepositoryInterface
{
    public function getActive(): Collection
    {
        return Campaign::active()->get();
    }

    public function getActiveForProduct(int $productId): Collection
    {
        return Campaign::active()->forProduct($productId)->get();
    }

    public function findById(int $id): ?Campaign
    {
        return Campaign::find($id);
    }

    public function incrementUsage(int $campaignId): void
    {
        Campaign::where('id', $campaignId)->increment('usage_count');
    }
}
