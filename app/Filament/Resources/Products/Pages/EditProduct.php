<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Filament\Traits\RedirectsToIndex;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Container\Attributes\Storage;

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
            ->title(__('filament/admin/product_resource.product_updated_successfully'))
            ->success()
            ->color('success') 
            ->send();
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {

        if (($data['type'] ?? 'simple') !== 'variable') {
                $product = $this->record->load(['primaryImage', 'images']);

                
                // Add primary image path if it exists
                if ($product->primaryImage) {
                    $data['primaryImage'] = $product->primaryImage->image_path;
                }
                            
                // Add gallery images if they exist
                $galleryImages = $product->images
                    ->where('is_primary', false)
                    ->pluck('image_path')
                    ->toArray();
                
                if (!empty($galleryImages)) {
                    $data['galleryImages'] = $galleryImages;
                }
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

        // $formData = $this->form->getState(); 
        // //dd($formData);

        
        // // Handle primary image
        // if (array_key_exists('primaryImage', $formData)) {

        //         $newImage = $formData['primaryImage']; // null | string | array
        //         //dd($newImage);

        //         // CASE 1: User removed the image
        //         if (empty($newImage)) {
        //             if ($product->primaryImage) {
        //                 Storage::disk('public')->delete($product->primaryImage->image_path);
        //                 $product->primaryImage->delete();
        //             }

        //             return;
        //         }

        //         // Normalize path (remove full URL if exists)
        //         $newImagePath = str_replace(asset('storage/') , '', $newImage);

        //         // CASE 2: New image uploaded (different from old)
        //         if (
        //             !$product->primaryImage ||
        //             $product->primaryImage->image_path !== $newImagePath
        //         ) {
        //             // Delete old image ONLY if a new one exists
        //             if ($product->primaryImage) {
        //                 Storage::disk('public')->delete($product->primaryImage->image_path);
        //             }

        //             $product->primaryImage()->updateOrCreate(
        //                 ['is_primary' => true],
        //                 ['image_path' => $newImagePath]
        //             );
        //         }
        //     }

        //     // Handle gallery images
        //     if (isset($formData['galleryImages']) && is_array($formData['galleryImages'])) {
        //         // Rest of your gallery images handling code...
        //     }

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
