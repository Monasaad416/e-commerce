<?php

namespace App\Filament\Resources\Products\Concerns;

trait ExtractsVariantTagIds
{
    /**
     * @return array<int, array<string, mixed>>
     */
    protected function indexProductVariantRowsById(?array $rows): array
    {
        $out = [];
        if (! is_array($rows)) {
            return $out;
        }
        foreach ($rows as $row) {
            if (! is_array($row) || ! isset($row['id'])) {
                continue;
            }
            $out[(int) $row['id']] = $row;
        }

        return $out;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function indexProductVariantRowsBySku(?array $rows): array
    {
        $out = [];
        if (! is_array($rows)) {
            return $out;
        }
        foreach ($rows as $row) {
            if (! is_array($row) || ! isset($row['sku'])) {
                continue;
            }
            $sku = trim((string) $row['sku']);
            if ($sku === '') {
                continue;
            }
            $out[$sku] = $row;
        }

        return $out;
    }

    /**
     * Prefer explicit `tag_ids` from dehydrated $data; if missing/empty, fall back to raw Livewire
     * (Wizard / relationship repeaters often omit nested keys in one of the two).
     *
     * @return list<int>
     */
    protected function resolveVariantTagIdsFromDataAndRaw(?array $fromData, ?array $fromRaw): array
    {
        if ($fromData !== null && array_key_exists('tag_ids', $fromData)) {
            return $this->extractVariantTagIdsFromRaw($fromData);
        }

        if ($fromData !== null) {
            $fromDehydrated = $this->extractVariantTagIdsFromRaw($fromData);
            if ($fromDehydrated !== []) {
                return $fromDehydrated;
            }
        }

        if ($fromRaw !== null) {
            return $this->extractVariantTagIdsFromRaw($fromRaw);
        }

        return [];
    }

    /**
     * Prefer dehydrated `$data` over Livewire raw state so submitted values win when they differ.
     *
     * @return list<int>
     */
    protected function extractSimpleProductTagIdsFromForm(array $data, array $rawState): array
    {
        if (array_key_exists('tag_id', $data)) {
            return $this->normalizeTagIds($data['tag_id']);
        }

        if (array_key_exists('tag_id', $rawState)) {
            return $this->normalizeTagIds($rawState['tag_id']);
        }

        if (isset($rawState['tags'])) {
            return $this->normalizeTagIdsFromRelationStyle($rawState['tags']);
        }

        return [];
    }

    /**
     * Filament raw state for relationship repeaters often exposes the loaded
     * relationship as `tags` without `tag_ids`; only using normalizeTagIds() then drops selections.
     *
     * @return list<int>
     */
    protected function extractVariantTagIdsFromRaw(array $variant): array
    {
        if (array_key_exists('tag_ids', $variant)) {
            return $this->normalizeTagIds($variant['tag_ids']);
        }

        if (array_key_exists('tags', $variant)) {
            return $this->normalizeTagIdsFromRelationStyle($variant['tags']);
        }

        return $this->normalizeTagIds($variant['variant_tag_id'] ?? null);
    }

    /**
     * @param mixed $tags list of ids, or serialized models/arrays with id
     *
     * @return list<int>
     */
    protected function normalizeTagIdsFromRelationStyle(mixed $tags): array
    {
        if ($tags === null || $tags === '' || $tags === []) {
            return [];
        }

        if (! is_array($tags)) {
            return $this->normalizeTagIds($tags);
        }

        $ids = [];
        foreach ($tags as $item) {
            if (is_int($item) || (is_string($item) && ctype_digit($item))) {
                $ids[] = (int) $item;

                continue;
            }
            if (is_array($item) && array_key_exists('id', $item)) {
                $ids[] = (int) $item['id'];

                continue;
            }
            if (is_object($item) && isset($item->id)) {
                $ids[] = (int) $item->id;
            }
        }

        return array_values(array_unique(array_filter($ids)));
    }
}
