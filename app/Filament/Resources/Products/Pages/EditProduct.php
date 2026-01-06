<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Traits\RedirectsToIndex;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditProduct extends EditRecord
{
    use RedirectsToIndex;
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title(__('filament/admin/product_resource.product_edited_successfully'))
            ->success()
            ->color('success') 
            ->send();
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (($data['type'] ?? 'simple') !== 'variable') {
            return $data;
        }

        $data['productVariants'] = $this->record
            ->productVariants
            ->map(function ($variant) {

                return [
                    'id' => $variant->id,
                    'qty' => $variant->qty,
                    'purchase_price' => $variant->purchase_price,
                    'selling_price' => $variant->selling_price,
                    'discount_price' => $variant->discount_price,
                    'sku' => $variant->sku,

        
                    'attributes' => $variant->attributes->map(function ($attribute) {
                        return [
                            'attribute_id' => $attribute->id,
                            'attribute_value_id' => $attribute->pivot->attribute_value_id,
                        ];
                    })->values()->toArray(),
                ];
            })
            ->toArray();

        return $data;
    }


    protected function afterSave(): void
    {
        $product = $this->record;

        if ($product->type !== 'variable') {
            return;
        }

        $submittedVariantIds = [];

        foreach ($this->data['productVariants'] ?? [] as $variantData) {

            // Update existing variant
            if (!empty($variantData['id'])) {
                $variant = $product->productVariants()
                    ->where('id', $variantData['id'])
                    ->first();

                if (!$variant) {
                    continue;
                }

                $variant->update([
                    'qty'            => $variantData['qty'],
                    'purchase_price' => $variantData['purchase_price'],
                    'selling_price'  => $variantData['selling_price'],
                    'discount_price' => $variantData['discount_price'] ?? null,
                    'sku'            => $variantData['sku'],
                    'is_active'      => true,
                    'is_featured'    => false,
                ]);
            }

            // Create new variant
            else {
                $variant = $product->productVariants()->create([
                    'qty'            => $variantData['qty'],
                    'purchase_price' => $variantData['purchase_price'],
                    'selling_price'  => $variantData['selling_price'],
                    'discount_price' => $variantData['discount_price'] ?? null,
                    'sku'            => $variantData['sku'],
                    'is_active'      => true,
                    'is_featured'    => false,
                ]);
            }

            $submittedVariantIds[] = $variant->id;

            // Sync attributes for this variant
            if (!empty($variantData['attributes'])) {
                $syncData = [];

                foreach ($variantData['attributes'] as $attribute) {
                    $syncData[$attribute['attribute_id']] = [
                        'attribute_value_id' => $attribute['attribute_value_id'],
                    ];
                }

                $variant->attributes()->sync($syncData);
            }
        }

        // Delete removed variants
        $product->productVariants()
            ->whereNotIn('id', $submittedVariantIds)
            ->delete();
    }


    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (($data['type'] ?? 'simple') === 'variable') {
            $data['qty'] = null;
            $data['purchase_price'] = null;
            $data['selling_price'] = null;
            $data['discount_price'] = null;
            $data['sku'] = null;
        } else {
            $data['productVariants'] = [];
        }

        return $data;
    }

}
