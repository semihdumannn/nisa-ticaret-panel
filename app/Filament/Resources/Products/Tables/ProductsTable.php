<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Brand;
use App\Models\Category;
use App\Modules\Product\Domain\ValueObjects\ProductUnit;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('images.image_url')
                    ->label('')
                    ->square()
                    ->size(48)
                    ->defaultImageUrl(fn ($record) => $record->images()->where('is_primary', true)->value('image_url')),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->description(fn ($record) => $record->sku),

                TextColumn::make('brand.name')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('price')
                    ->money('TRY')
                    ->sortable(),

                TextColumn::make('unit')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ProductUnit::tryFrom($state)?->label() ?? $state),

                TextColumn::make('variants_count')
                    ->counts('variants')
                    ->label('Variants')
                    ->badge()
                    ->color('gray'),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-minus'),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('brand_id')
                    ->label('Brand')
                    ->options(Brand::active()->ordered()->pluck('name', 'id')),

                SelectFilter::make('categories')
                    ->label('Category')
                    ->relationship('categories', 'name')
                    ->options(Category::active()->ordered()->pluck('name', 'id')),

                TernaryFilter::make('is_featured')->label('Featured'),
                TernaryFilter::make('is_active')->label('Status'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),

                Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function ($record) {
                        $clone = $record->replicate(['slug', 'sku', 'barcode']);
                        $clone->name .= ' (Copy)';
                        $clone->save();

                        $record->categories->each(fn ($cat) => $clone->categories()->attach($cat->id));
                    })
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
