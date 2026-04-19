<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\Concerns\ExtractsVariantTagIds;
use App\Filament\Resources\Products\ProductResource;
use App\Filament\Traits\RedirectsToIndex;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class EditProduct extends EditRecord
{
    use ExtractsVariantTagIds;
    use RedirectsToIndex;
    protected static string $resource = ProductResource::class;
    /** @var list<int> */
    protected array $pendingSimpleTagIds = [];
    /** @var array<int, list<int>> */
    protected array $pendingVariantTagsById = [];

    protected function debugLog(string $hypothesisId, string $location, string $message, array $data = []): void
    {
        file_put_contents(base_path('debug-55ac17.log'), json_encode([
            'sessionId' => '55ac17',
            'runId' => 'run-debug-1',
            'hypothesisId' => $hypothesisId,
            'location' => $location,
            'message' => $message,
            'data' => $data,
            'timestamp' => (int) (microtime(true) * 1000),
        ]) . "\n", FILE_APPEND | LOCK_EX);
    }
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
            $product = $this->record->load(['primaryImage', 'images', 'tags']);

            if ($product->primaryImage) {
                $data['primaryImage'] = $product->primaryImage->image_path;
            }

            $galleryImages = $product->images
                ->where('is_featured', false)
                ->pluck('image_path')
                ->toArray();

            if (!empty($galleryImages)) {
                $data['galleryImages'] = $galleryImages;
            }

            $data['tag_id'] = $product->tags()->pluck('tags.id')->all();

            return $data;
        }

        $data['productVariants'] = $this->record
            ->productVariants()
            ->with(['variantAttributeValues', 'variantImages', 'tags'])
            ->get()
            ->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'qty' => $variant->qty,
                    'purchase_price' => $variant->purchase_price,
                    'selling_price' => $variant->selling_price,
                    'discount_price' => $variant->discount_price,
                    'sku' => $variant->sku,
                    'is_active' => $variant->is_active,
                    'is_featured' => $variant->is_featured,
                    'tag_ids' => $variant->tags->pluck('id')->all(),
                ];
            })
            ->toArray();

        return $data;
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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $rawState = $this->form->getRawState();
        $this->pendingSimpleTagIds = $this->extractSimpleProductTagIdsFromForm($data, $rawState);
        $this->pendingVariantTagsById = [];

        $byIdFromData = $this->indexProductVariantRowsById($data['productVariants'] ?? null);
        $byIdFromRaw = $this->indexProductVariantRowsById($rawState['productVariants'] ?? null);
        foreach (array_unique([...array_keys($byIdFromData), ...array_keys($byIdFromRaw)]) as $variantId) {
            $this->pendingVariantTagsById[$variantId] = $this->resolveVariantTagIdsFromDataAndRaw(
                $byIdFromData[$variantId] ?? null,
                $byIdFromRaw[$variantId] ?? null,
            );
        }

        // #region agent log
        $this->debugLog(
            'H1',
            'EditProduct.php:mutateFormDataBeforeSave',
            'resolved_pending_tags_from_form',
            [
                'product_id' => $this->record?->id,
                'type' => $data['type'] ?? null,
                'simple_tag_ids' => $this->pendingSimpleTagIds,
                'variant_tag_map' => $this->pendingVariantTagsById,
                'variant_ids_from_data' => array_keys($byIdFromData),
                'variant_ids_from_raw' => array_keys($byIdFromRaw),
            ],
        );
        // #endregion

        unset($data['tag_id']);

        if (isset($data['productVariants']) && is_array($data['productVariants'])) {
            foreach ($data['productVariants'] as &$variant) {
                unset($variant['variant_tag_id']);
                unset($variant['tags']);
                unset($variant['tag_ids']);
            }
            unset($variant);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->refresh();

        $product = $this->record;
        // #region agent log
        $this->debugLog(
            'H2',
            'EditProduct.php:afterSave',
            'after_save_branch_selected',
            [
                'product_id' => $product->id,
                'type' => $product->type,
                'pending_simple' => $this->pendingSimpleTagIds,
                'pending_variant' => $this->pendingVariantTagsById,
            ],
        );
        // #endregion
        if ($product->type === 'simple') {
            DB::transaction(function () use ($product): void {
                $product->tags()->sync($this->pendingSimpleTagIds);
            });
            DB::afterCommit(function () use ($product): void {
                // #region agent log
                $rows = DB::table('product_tags')
                    ->where('product_id', $product->id)
                    ->whereNull('product_variant_id')
                    ->get(['product_id', 'product_variant_id', 'tag_id'])
                    ->all();
                $this->debugLog(
                    'H12',
                    'EditProduct.php:afterSave',
                    'simple_product_tags_after_commit',
                    [
                        'product_id' => $product->id,
                        'rows' => $rows,
                    ],
                );
                // #endregion
            });
            // #region agent log
            $simpleRows = DB::table('product_tags')
                ->where('product_id', $product->id)
                ->whereNull('product_variant_id')
                ->get(['product_id', 'product_variant_id', 'tag_id'])
                ->all();
            $this->debugLog(
                'H3',
                'EditProduct.php:afterSave',
                'simple_product_tags_after_sync',
                [
                    'product_id' => $product->id,
                    'rows' => $simpleRows,
                ],
            );
            // #endregion
            return;
        }

        if ($product->type !== 'variable') {
            return;
        }

        // Only remove product-level tags (variant rows use product_variant_id NOT NULL)
        DB::table('product_tags')
            ->where('product_id', $product->id)
            ->whereNull('product_variant_id')
            ->delete();

        $variantsById = $product->productVariants()->get()->keyBy('id');
        // #region agent log
        $this->debugLog(
            'H4',
            'EditProduct.php:afterSave',
            'variable_variants_loaded',
            [
                'product_id' => $product->id,
                'variant_ids_in_db' => $variantsById->keys()->all(),
            ],
        );
        // #endregion

        DB::transaction(function () use ($variantsById): void {
            foreach ($this->pendingVariantTagsById as $variantId => $tagIds) {
                if (! isset($variantsById[$variantId])) { continue; }

                $variant = $variantsById[$variantId];
                $payload = [];
                foreach ($tagIds as $tagId) {
                    $payload[$tagId] = ['product_id' => $variant->product_id];
                }
                $variant->tags()->sync($payload);
                // #region agent log
                $variantRows = DB::table('product_tags')
                    ->where('product_variant_id', $variant->id)
                    ->get(['product_id', 'product_variant_id', 'tag_id'])
                    ->all();
                $this->debugLog(
                    'H5',
                    'EditProduct.php:afterSave',
                    'variant_tags_after_sync',
                    [
                        'variant_id' => $variant->id,
                        'expected_tag_ids' => $tagIds,
                        'rows' => $variantRows,
                    ],
                );
                // #endregion
            }
        });

        DB::afterCommit(function () use ($product): void {
            // #region agent log
            $rows = DB::table('product_tags')
                ->where('product_id', $product->id)
                ->whereNotNull('product_variant_id')
                ->get(['product_id', 'product_variant_id', 'tag_id'])
                ->all();
            $this->debugLog(
                'H13',
                'EditProduct.php:afterSave',
                'variable_product_tags_after_commit',
                [
                    'product_id' => $product->id,
                    'rows' => $rows,
                ],
            );
            // #endregion
        });
    }
}
