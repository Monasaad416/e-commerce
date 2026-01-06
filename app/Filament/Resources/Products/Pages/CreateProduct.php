<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Traits\RedirectsToIndex;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateProduct extends CreateRecord
{
    use RedirectsToIndex;
    protected static string $resource = ProductResource::class;
    protected static bool $canCreateAnother = false;

    //  hide default buttons (create, cancel) and depent on wizard submit
    protected function getFormActions(): array
    {
        return [];
    }
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title(__('filament/admin/product_resource.product_created_successfully'))
            ->success()
            ->color('success') 
            ->send();
    }

    protected function afterCreate(): void
    {
        $product = $this->record;
        $allFormData = $this->form->getState();

        // Handle SIMPLE products
        if ($product->type === 'simple') {
            // 1. Handle simple product primary image
            if (!empty($allFormData['primaryImage'])) {
                $product->images()->create([
                    'image_path' => $allFormData['primaryImage'],
                    'is_primary' => true,
                ]);
            }

            // 2. Handle simple product gallery images
            if (!empty($allFormData['galleryImages']) && is_array($allFormData['galleryImages'])) {
                $galleryImages = [];
                foreach ($allFormData['galleryImages'] as $imagePath) {
                    if ($imagePath !== ($allFormData['primaryImage'] ?? null)) {
                        $galleryImages[] = ['image_path' => $imagePath, 'is_primary' => false];
                    }
                }
                if (!empty($galleryImages)) {
                    $product->images()->createMany($galleryImages);
                }
            }
            return; // Stop execution for simple products
        }

        // Handle VARIABLE products
        if ($product->type === 'variable' && !empty($allFormData['productVariants'])) {
            foreach ($allFormData['productVariants'] as $variantData) {
                // 1. Create the variant
                $variant = $product->productVariants()->create([
                    'qty' => $variantData['qty'] ?? 0,
                    'purchase_price' => $variantData['purchase_price'] ?? 0,
                    'selling_price' => $variantData['selling_price'] ?? 0,
                    'discount_price' => $variantData['discount_price'] ?? null,
                    'sku' => $variantData['sku'] ?? null,
                    'is_active' => $variantData['is_active'] ?? true,
                    'is_featured' => $variantData['is_featured'] ?? false,
                ]);

                // 2. Handle attributes
                if (!empty($variantData['productAttributes']) && is_array($variantData['productAttributes'])) {
                    $attributesToSync = [];
                    foreach ($variantData['productAttributes'] as $attribute) {
                        if (!empty($attribute['attribute_id']) && !empty($attribute['attribute_value_id'])) {
                            $attributesToSync[$attribute['attribute_id']] = ['attribute_value_id' => $attribute['attribute_value_id']];
                        }
                    }
                    if (!empty($attributesToSync)) {
                        $variant->attributes()->sync($attributesToSync);
                    }
                }

                // 3. Handle variant primary image
                if (!empty($variantData['variantPrimaryImage'])) {
                    $variant->variantImages()->create([
                        'image_path' => $variantData['variantPrimaryImage'],
                        'is_primary' => true,
                    ]);
                }

                // 4. Handle variant gallery images
                if (!empty($variantData['variantImages']) && is_array($variantData['variantImages'])) {
                    $galleryImages = [];
                    foreach ($variantData['variantImages'] as $imagePath) {
                        if ($imagePath !== ($variantData['variantPrimaryImage'] ?? null)) {
                            $galleryImages[] = ['image_path' => $imagePath, 'is_primary' => false];
                        }
                    }
                    if (!empty($galleryImages)) {
                        $variant->variantImages()->createMany($galleryImages);
                    }
                }
            }
        }
    }

protected function processVariantImages($variant, $variantData)
{
    \Log::info('Processing images for variant:', [
        'variant_id' => $variant->id,
        'has_primary' => isset($variantData['variantPrimaryImage']),
        'has_gallery' => isset($variantData['variantImages'])
    ]);

    // Handle primary image
    if (!empty($variantData['variantPrimaryImage'])) {
        $variant->variantImages()->create([
            'image_path' => $variantData['variantPrimaryImage'],
            'is_primary' => true
        ]);
        \Log::info('Created primary image', ['path' => $variantData['variantPrimaryImage']]);
    }

    // Handle gallery images
if (
    !empty($variantData['variantPrimaryImage']) &&
    !str_contains($variantData['variantPrimaryImage'], 'livewire-tmp')
) {
    $variant->variantImages()->create([
        'image_path' => $variantData['variantPrimaryImage'],
        'is_primary' => true,
    ]);
}

}

protected function mutateFormDataBeforeCreate(array $data): array
{
    // Handle product type specific logic
    if (($data['type'] ?? 'simple') === 'variable') {
        $data['qty'] = null;
        $data['purchase_price'] = null;
        $data['selling_price'] = null;
        $data['discount_price'] = null;
        $data['sku'] = null;
    } else {
        $data['productVariants'] = [];
        return $data;
    }

    // Process variants if they exist
    if (isset($data['productVariants']) && is_array($data['productVariants'])) {
        foreach ($data['productVariants'] as &$variant) {
            // Handle primary image
            if (isset($variant['variantPrimaryImage'])) {
                if (!is_string($variant['variantPrimaryImage']) || !str_starts_with($variant['variantPrimaryImage'], 'variants/')) {
                    unset($variant['variantPrimaryImage']);
                }
            }

            // Handle gallery images
            if (isset($variant['variantImages']) && is_array($variant['variantImages'])) {
                $variant['variantImages'] = array_filter($variant['variantImages'], function($image) {
                    return is_string($image) && str_starts_with($image, 'variants/');
                });
            } else {
                $variant['variantImages'] = [];
            }
        }
        unset($variant);
    }

    return $data;
}




}
