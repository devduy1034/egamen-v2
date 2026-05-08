<?php

namespace LARAVEL\Services;

use LARAVEL\Models\UserEventModel;

class UserEventRepository
{
    /**
     * @return array<int,array<string,mixed>>
     */
    public function getEventsForIdentity(?string $userId, ?string $anonymousId, int $limit = 300, ?string $sessionId = null): array
    {
        $limit = max(1, $limit);
        $query = UserEventModel::query()
            ->select('event_id', 'event_type', 'user_id', 'anonymous_id', 'session_id', 'source', 'metadata', 'created_at')
            ->orderBy('id', 'desc')
            ->limit($limit);

        $hasCondition = false;
        if ($userId !== null && trim($userId) !== '') {
            $query->where('user_id', trim($userId));
            $hasCondition = true;
        }
        if ($anonymousId !== null && trim($anonymousId) !== '') {
            if ($hasCondition) {
                $query->orWhere('anonymous_id', trim($anonymousId));
            } else {
                $query->where('anonymous_id', trim($anonymousId));
                $hasCondition = true;
            }
        }
        if ($sessionId !== null && trim($sessionId) !== '') {
            if ($hasCondition) {
                $query->orWhere('session_id', trim($sessionId));
            } else {
                $query->where('session_id', trim($sessionId));
                $hasCondition = true;
            }
        }
        if (!$hasCondition) {
            return [];
        }

        return $query->get()->map(function ($row) {
            $metadata = $row->metadata;
            if (!is_array($metadata)) {
                $decoded = json_decode((string) $metadata, true);
                $metadata = is_array($decoded) ? $decoded : [];
            }
            return [
                'event_id' => (string) ($row->event_id ?? ''),
                'event_type' => (string) ($row->event_type ?? ''),
                'user_id' => (string) ($row->user_id ?? ''),
                'anonymous_id' => (string) ($row->anonymous_id ?? ''),
                'session_id' => (string) ($row->session_id ?? ''),
                'source' => (string) ($row->source ?? ''),
                'metadata' => $metadata,
                'created_at' => (string) ($row->created_at ?? ''),
            ];
        })->values()->all();
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function getRecentPurchaseEvents(int $limit = 300): array
    {
        $limit = max(1, $limit);
        return UserEventModel::query()
            ->select('metadata', 'created_at')
            ->where('event_type', EventSchemaValidator::EVENT_PURCHASE)
            ->orderBy('id', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $metadata = $row->metadata;
                if (!is_array($metadata)) {
                    $decoded = json_decode((string) $metadata, true);
                    $metadata = is_array($decoded) ? $decoded : [];
                }
                return [
                    'metadata' => $metadata,
                    'created_at' => (string) ($row->created_at ?? ''),
                ];
            })->values()->all();
    }

    /**
     * @return array<int,int>
     */
    public function getTrendingProductIds(int $limit = 60): array
    {
        $limit = max(1, $limit);
        $events = UserEventModel::query()
            ->select('event_type', 'metadata')
            ->whereIn('event_type', [
                EventSchemaValidator::EVENT_PRODUCT_VIEW,
                EventSchemaValidator::EVENT_CLICK_RESULT,
                EventSchemaValidator::EVENT_ADD_TO_CART,
                EventSchemaValidator::EVENT_WISHLIST_ADD,
                EventSchemaValidator::EVENT_PURCHASE,
            ])
            ->orderBy('id', 'desc')
            ->limit(3000)
            ->get();

        $weights = [
            EventSchemaValidator::EVENT_PRODUCT_VIEW => 1,
            EventSchemaValidator::EVENT_CLICK_RESULT => 2,
            EventSchemaValidator::EVENT_WISHLIST_ADD => 4,
            EventSchemaValidator::EVENT_ADD_TO_CART => 5,
            EventSchemaValidator::EVENT_PURCHASE => 8,
        ];
        $scores = [];

        foreach ($events as $event) {
            $eventType = (string) ($event->event_type ?? '');
            $weight = $weights[$eventType] ?? 1;
            $metadata = $event->metadata;
            if (!is_array($metadata)) {
                $decoded = json_decode((string) $metadata, true);
                $metadata = is_array($decoded) ? $decoded : [];
            }

            if ($eventType === EventSchemaValidator::EVENT_PURCHASE) {
                foreach ((array) ($metadata['items'] ?? []) as $item) {
                    $productId = (int) ($item['product_id'] ?? 0);
                    if ($productId <= 0) {
                        continue;
                    }
                    $scores[$productId] = ($scores[$productId] ?? 0) + $weight;
                }
                continue;
            }

            $productId = (int) ($metadata['product_id'] ?? 0);
            if ($productId <= 0) {
                continue;
            }
            $scores[$productId] = ($scores[$productId] ?? 0) + $weight;
        }

        arsort($scores);
        return array_slice(array_map('intval', array_keys($scores)), 0, $limit);
    }
}
