<?php

namespace App\Modules\Campaign\Domain\Contracts;

use App\Models\Campaign;
use Illuminate\Support\Collection;

interface CampaignRepositoryInterface
{
    /** Return all currently active campaigns. */
    public function getActive(): Collection;

    /** Active campaigns that cover a specific product. */
    public function getActiveForProduct(int $productId): Collection;

    public function findById(int $id): ?Campaign;

    public function incrementUsage(int $campaignId): void;
}
