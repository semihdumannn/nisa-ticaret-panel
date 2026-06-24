<?php

namespace App\Console\Commands;

use App\Modules\Subscription\Application\UseCases\ProcessDueSubscriptionsUseCase;
use Illuminate\Console\Command;

class ProcessSubscriptionOrdersCommand extends Command
{
    protected $signature   = 'subscriptions:process-orders';
    protected $description = 'Create orders for subscriptions due today';

    public function handle(ProcessDueSubscriptionsUseCase $useCase): int
    {
        $result = $useCase->execute();
        $this->info("Processed: {$result['processed']} subscriptions, {$result['skipped']} skipped (stock).");

        return Command::SUCCESS;
    }
}
