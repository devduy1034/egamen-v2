<?php

namespace LARAVEL\Traits;

use Illuminate\Support\Collection;
use LARAVEL\Models\ProductPropertiesModel;

trait TraitOrderInventory
{
    protected function reserveOrderInventory($orderDetail): array
    {
        $variantLines = $this->collectOrderVariantLines($orderDetail);
        if (empty($variantLines)) {
            return ['status' => true, 'message' => ''];
        }

        $appliedAdjustments = [];
        foreach ($variantLines as $line) {
            $rowId = (int) ($line['row_id'] ?? 0);
            $qty = max(0, (int) ($line['qty'] ?? 0));
            if ($rowId <= 0 || $qty <= 0) {
                continue;
            }

            $updated = ProductPropertiesModel::where('id', $rowId)
                ->where('quantity', '>=', $qty)
                ->decrement('quantity', $qty);

            if (!$updated) {
                $this->rollbackReservedInventory($appliedAdjustments);
                return [
                    'status' => false,
                    'message' => 'So luong ton kho da thay doi. Vui long kiem tra lai gio hang.',
                ];
            }

            $appliedAdjustments[] = [
                'row_id' => $rowId,
                'qty' => $qty,
            ];
        }

        return ['status' => true, 'message' => ''];
    }

    protected function releaseOrderInventory($orderDetail): void
    {
        $variantLines = $this->collectOrderVariantLines($orderDetail);
        if (empty($variantLines)) {
            return;
        }

        foreach ($variantLines as $line) {
            $rowId = (int) ($line['row_id'] ?? 0);
            $qty = max(0, (int) ($line['qty'] ?? 0));
            if ($rowId <= 0 || $qty <= 0) {
                continue;
            }

            ProductPropertiesModel::where('id', $rowId)->increment('quantity', $qty);
        }
    }

    protected function hasReservedInventoryFlag($infoUser): bool
    {
        if ($infoUser instanceof Collection) {
            $infoUser = $infoUser->toArray();
        }
        if (!is_array($infoUser)) {
            return false;
        }

        return (int) ($infoUser['_inventory_reserved'] ?? 0) === 1;
    }

    protected function markReservedInventoryFlag($infoUser): array
    {
        if ($infoUser instanceof Collection) {
            $infoUser = $infoUser->toArray();
        }
        if (!is_array($infoUser)) {
            $infoUser = [];
        }

        $infoUser['_inventory_reserved'] = 1;
        return $infoUser;
    }

    protected function clearReservedInventoryFlag($infoUser): array
    {
        if ($infoUser instanceof Collection) {
            $infoUser = $infoUser->toArray();
        }
        if (!is_array($infoUser)) {
            $infoUser = [];
        }

        $infoUser['_inventory_reserved'] = 0;
        return $infoUser;
    }

    protected function isCanceledStatusTransition(int $currentStatusId, int $nextStatusId, $orderStatuses): bool
    {
        if ($currentStatusId <= 0 || $nextStatusId <= 0 || $currentStatusId === $nextStatusId) {
            return false;
        }

        $isCurrentCanceled = $currentStatusId === 5 || $this->statusHasAlias($orderStatuses, $currentStatusId, 'canceled');
        $isNextCanceled = $nextStatusId === 5 || $this->statusHasAlias($orderStatuses, $nextStatusId, 'canceled');

        return !$isCurrentCanceled && $isNextCanceled;
    }

    protected function statusHasAlias($orderStatuses, int $statusId, string $alias): bool
    {
        if ($statusId <= 0 || $alias === '') {
            return false;
        }

        $orderStatuses = $orderStatuses instanceof Collection ? $orderStatuses : collect($orderStatuses);
        $status = $orderStatuses->first(function ($row) use ($statusId) {
            return (int) ($row['id'] ?? $row->id ?? 0) === $statusId;
        });
        if (empty($status)) {
            return false;
        }

        $name = (string) ($status['namevi'] ?? $status->namevi ?? '');
        return $this->resolveOrderStatusAlias((string) $name) === $alias;
    }

    protected function rollbackReservedInventory(array $appliedAdjustments): void
    {
        foreach ($appliedAdjustments as $line) {
            $rowId = (int) ($line['row_id'] ?? 0);
            $qty = max(0, (int) ($line['qty'] ?? 0));
            if ($rowId <= 0 || $qty <= 0) {
                continue;
            }
            ProductPropertiesModel::where('id', $rowId)->increment('quantity', $qty);
        }
    }

    protected function collectOrderVariantLines($orderDetail): array
    {
        if ($orderDetail instanceof Collection) {
            $orderDetail = $orderDetail->toArray();
        } elseif (is_object($orderDetail)) {
            $orderDetail = (array) $orderDetail;
        }

        if (!is_array($orderDetail)) {
            return [];
        }

        $aggregated = [];
        foreach (array_values($orderDetail) as $item) {
            $itemData = is_array($item) ? $item : (array) $item;
            $qty = max(0, (int) data_get($itemData, 'qty', 0));
            if ($qty <= 0) {
                continue;
            }

            $productId = (int) (data_get($itemData, 'options.itemProduct.id') ?? data_get($itemData, 'id', 0));
            if ($productId <= 0) {
                continue;
            }

            $propertyIds = $this->extractPropertyIds(data_get($itemData, 'options.properties', []));
            if (empty($propertyIds)) {
                continue;
            }

            $variantRow = $this->resolveVariantRowByProperties($productId, $propertyIds);
            if (empty($variantRow?->id)) {
                continue;
            }

            $rowId = (int) $variantRow->id;
            if (empty($aggregated[$rowId])) {
                $aggregated[$rowId] = ['row_id' => $rowId, 'qty' => 0];
            }
            $aggregated[$rowId]['qty'] += $qty;
        }

        return array_values($aggregated);
    }

    protected function extractPropertyIds($properties): array
    {
        if ($properties instanceof Collection) {
            $properties = $properties->toArray();
        } elseif (is_object($properties)) {
            $properties = (array) $properties;
        }

        if (!is_array($properties)) {
            return [];
        }

        return collect($properties)
            ->map(function ($property) {
                if ($property instanceof Collection) {
                    $property = $property->toArray();
                } elseif (is_object($property)) {
                    $property = (array) $property;
                }

                return (int) (data_get($property, 'id', 0));
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function resolveVariantRowByProperties(int $productId, array $propertyIds): ?ProductPropertiesModel
    {
        $productId = (int) $productId;
        $propertyIds = array_values(array_unique(array_filter(array_map('intval', $propertyIds))));
        if ($productId <= 0 || empty($propertyIds)) {
            return null;
        }

        $query = ProductPropertiesModel::select('id', 'quantity')
            ->where('id_parent', $productId);

        foreach ($propertyIds as $propertyId) {
            $query->whereRaw('FIND_IN_SET(?, id_properties)', [$propertyId]);
        }

        $query->whereRaw(
            "(LENGTH(id_properties) - LENGTH(REPLACE(id_properties, ',', '')) + 1) = ?",
            [count($propertyIds)],
        );

        return $query->first();
    }
}
