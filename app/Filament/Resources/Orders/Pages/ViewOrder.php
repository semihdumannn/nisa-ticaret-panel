<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Modules\Order\Application\UseCases\UpdateOrderStatusUseCase;
use App\Modules\Order\Domain\ValueObjects\OrderStatus;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        /** @var \App\Models\Order $order */
        $order         = $this->getRecord();
        $currentStatus = OrderStatus::from($order->status);
        $transitions   = $currentStatus->allowedTransitions();

        $actions = [];

        // One button per valid next status
        foreach ($transitions as $nextStatus) {
            $actions[] = Action::make('transition_' . $nextStatus->value)
                ->label($nextStatus->label())
                ->icon($this->transitionIcon($nextStatus))
                ->color($this->transitionColor($nextStatus))
                ->requiresConfirmation($nextStatus === OrderStatus::CANCELLED)
                ->modalHeading(fn () => 'Cancel this order?')
                ->modalDescription(fn () => 'This action cannot be undone.')
                ->action(function () use ($order, $nextStatus) {
                    try {
                        app(UpdateOrderStatusUseCase::class)->execute(
                            order:     $order,
                            newStatus: $nextStatus,
                            note:      'Updated via admin panel.',
                            userId:    auth()->id(),
                        );

                        $this->refreshRecord();

                        Notification::make()
                            ->title('Status updated to "' . $nextStatus->label() . '"')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Could not update status')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                });
        }

        $actions[] = EditAction::make()->outlined();

        return $actions;
    }

    private function transitionIcon(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::CONFIRMED  => 'heroicon-o-check-circle',
            OrderStatus::PREPARING  => 'heroicon-o-wrench-screwdriver',
            OrderStatus::ON_THE_WAY => 'heroicon-o-truck',
            OrderStatus::DELIVERED  => 'heroicon-o-check-badge',
            OrderStatus::CANCELLED  => 'heroicon-o-x-circle',
            default                 => 'heroicon-o-arrow-right',
        };
    }

    private function transitionColor(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::CONFIRMED  => 'info',
            OrderStatus::PREPARING  => 'warning',
            OrderStatus::ON_THE_WAY => 'primary',
            OrderStatus::DELIVERED  => 'success',
            OrderStatus::CANCELLED  => 'danger',
            default                 => 'gray',
        };
    }
}
