<?php

namespace App\Modules\Campaign\Application\UseCases;

use App\Models\Campaign;
use App\Modules\Campaign\Domain\Contracts\CampaignRepositoryInterface;

class CalculateOrderDiscountUseCase
{
    public function __construct(private readonly CampaignRepositoryInterface $campaigns) {}

    /**
     * Calculate the best campaign discount applicable to the given subtotal.
     * Returns the highest discount among all active campaigns (greedy pick).
     *
     * @return array{discount: float, campaign: ?Campaign}
     */
    public function execute(float $subtotal, ?int $productId = null): array
    {
        $campaigns = $productId
            ? $this->campaigns->getActiveForProduct($productId)
            : $this->campaigns->getActive();

        $bestDiscount = 0.0;
        $bestCampaign = null;

        foreach ($campaigns as $campaign) {
            $discount = $campaign->calculateDiscount($subtotal);
            if ($discount > $bestDiscount) {
                $bestDiscount = $discount;
                $bestCampaign = $campaign;
            }
        }

        return ['discount' => $bestDiscount, 'campaign' => $bestCampaign];
    }
}
