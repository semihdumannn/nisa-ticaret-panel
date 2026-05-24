<?php

namespace App\Modules\Campaign\Application\UseCases;

use App\Modules\Campaign\Domain\Contracts\CampaignRepositoryInterface;
use Illuminate\Support\Collection;

class GetActiveCampaignsUseCase
{
    public function __construct(private readonly CampaignRepositoryInterface $campaigns) {}

    public function execute(?int $productId = null): Collection
    {
        if ($productId !== null) {
            return $this->campaigns->getActiveForProduct($productId);
        }

        return $this->campaigns->getActive();
    }
}
