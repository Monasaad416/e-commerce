<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\Concerns\ExtractsVariantTagIds;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Traits\RedirectsToIndex;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class CreateProduct extends CreateRecord
{
    use ExtractsVariantTagIds;
    use RedirectsToIndex;
    protected static string $resource = ProductResource::class;
    protected static bool $canCreateAnother = false;
    /** @var list<int> */
    protected array $pendingSimpleTagIds = [];
    /** @var array<string, list<int>> */
    protected array $pendingVariantTagsBySku = [];

    protected function debugLog(string $hypothesisId, string $location, string $message, array $data = []): void
    {
        file_put_contents(base_path('debug-55ac17.log'), json_encode([
            'sessionId' => '55ac17',
            'runId' => 'run-debug-2',
            'hypothesisId' => $hypothesisId,
            'location' => $location,
            'message' => $message,
            'data' => $data,
            'timestamp' => (int) (microtime(true) * 1000),
        ]) . "\n", FILE_APPEND | LOCK_EX);
    }


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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $rawState = $this->form->getRawState();
        $this->pendingSimpleTagIds = $this->extractSimpleProductTagIdsFromForm($data, $rawState);
        $this->pendingVariantTagsBySku = [];

        $bySkuFromData = $this->indexProductVariantRowsBySku($data['productVariants'] ?? null);
        $bySkuFromRaw = $this->indexProductVariantRowsBySku($rawState['productVariants'] ?? null);
        foreach (array_unique([...array_keys($bySkuFromData), ...array_keys($bySkuFromRaw)]) as $sku) {
            $this->pendingVariantTagsBySku[$sku] = $this->resolveVariantTagIdsFromDataAndRaw(
                $bySkuFromData[$sku] ?? null,
                $bySkuFromRaw[$sku] ?? null,
            );
        }
        // #region agent log
        $this->debugLog(
            'H6',
            'CreateProduct.php:mutateFormDataBeforeCreate',
            'create_pending_tags_from_form',
            [
                'type' => $data['type'] ?? null,
                'simple_tag_ids' => $this->pendingSimpleTagIds,
                'variant_tags_by_sku' => $this->pendingVariantTagsBySku,
                'sku_from_data' => array_keys($bySkuFromData),
                'sku_from_raw' => array_keys($bySkuFromRaw),
            ],
        );
        // #endregion

        unset($data['tag_id']);

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

        if (isset($data['productVariants']) && is_array($data['productVariants'])) {
            foreach ($data['productVariants'] as &$variant) {
                unset($variant['variant_tag_id']);
                unset($variant['tags']);
                unset($variant['tag_ids']);

                if (isset($variant['variantPrimaryImage'])) {
                    if (! is_string($variant['variantPrimaryImage']) || ! str_starts_with($variant['variantPrimaryImage'], 'variants/')) {
                        unset($variant['variantPrimaryImage']);
                    }
                }

                if (isset($variant['variantImages']) && is_array($variant['variantImages'])) {
                    $variant['variantImages'] = array_filter($variant['variantImages'], function ($image) {
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

    protected function afterCreate(): void
    {
        $product = $this->record;
        $allFormData = $this->form->getState();
        // #region agent log
        $this->debugLog(
            'H7',
            'CreateProduct.php:afterCreate',
            'create_after_branch_selected',
            [
                'product_id' => $product->id,
                'type' => $product->type,
                'pending_simple' => $this->pendingSimpleTagIds,
                'pending_variant' => $this->pendingVariantTagsBySku,
            ],
        );
        // #endregion

        if ($product->type === 'simple') {
            if (!empty($allFormData['primaryImage'])) {
                $product->images()->create([
                    'image_path' => $allFormData['primaryImage'],
                    'is_featured' => true,
                ]);
            }

            if (!empty($allFormData['galleryImages']) && is_array($allFormData['galleryImages'])) {
                $galleryImages = [];
                foreach ($allFormData['galleryImages'] as $imagePath) {
                    if ($imagePath !== ($allFormData['primaryImage'] ?? null)) {
                        $galleryImages[] = ['image_path' => $imagePath, 'is_featured' => false];
                    }
                }
                if (!empty($galleryImages)) {
                    $product->images()->createMany($galleryImages);
                }
            }

            DB::transaction(function () use ($product): void {
                $product->tags()->sync($this->pendingSimpleTagIds);
            });
            // #region agent log
            $rows = DB::table('product_tags')
                ->where('product_id', $product->id)
                ->whereNull('product_variant_id')
                ->get(['product_id', 'product_variant_id', 'tag_id'])
                ->all();
            $this->debugLog('H8', 'CreateProduct.php:afterCreate', 'create_simple_tags_after_sync', [
                'product_id' => $product->id,
                'rows' => $rows,
            ]);
            // #endregion
            return;
        }

        if ($product->type !== 'variable') {
            return;
        }

        // Variable products:
        // Filament already creates variants/attributes/images via relationship repeaters.
        // We only sync variant tags here.
        $variantsBySku = $product->productVariants()->get()->keyBy('sku');

        DB::transaction(function () use ($variantsBySku): void {
            foreach ($this->pendingVariantTagsBySku as $sku => $tagIds) {
                if (! isset($variantsBySku[$sku])) { continue; }

                $variant = $variantsBySku[$sku];
                $payload = [];
                foreach ($tagIds as $tagId) {
                    $payload[$tagId] = ['product_id' => $variant->product_id];
                }
                $variant->tags()->sync($payload);
                // #region agent log
                $rows = DB::table('product_tags')
                    ->where('product_variant_id', $variant->id)
                    ->get(['product_id', 'product_variant_id', 'tag_id'])
                    ->all();
                $this->debugLog('H9', 'CreateProduct.php:afterCreate', 'create_variant_tags_after_sync', [
                    'variant_id' => $variant->id,
                    'sku' => $sku,
                    'expected_tag_ids' => $tagIds,
                    'rows' => $rows,
                ]);
                // #endregion
            }
        });
    }

    /** @return list<int> */
    protected function normalizeTagIds(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return array_values(array_filter(array_map('intval', $value)));
        }

        return [(int) $value];
    }
}
