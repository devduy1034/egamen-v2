<?php

namespace LARAVEL\Services;

class EventSchemaValidator
{
    public const EVENT_SEARCH_QUERY = 'search_query';
    public const EVENT_CLICK_RESULT = 'click_result';
    public const EVENT_PRODUCT_VIEW = 'product_view';
    public const EVENT_ADD_TO_CART = 'add_to_cart';
    public const EVENT_WISHLIST_ADD = 'wishlist_add';
    public const EVENT_PURCHASE = 'purchase';

    public function allowedEventTypes(): array
    {
        return [
            self::EVENT_SEARCH_QUERY,
            self::EVENT_CLICK_RESULT,
            self::EVENT_PRODUCT_VIEW,
            self::EVENT_ADD_TO_CART,
            self::EVENT_WISHLIST_ADD,
            self::EVENT_PURCHASE,
        ];
    }

    /**
     * @return array{valid:bool,errors:array<int,string>}
     */
    public function validate(string $eventType, array $metadata): array
    {
        $errors = [];
        if (!in_array($eventType, $this->allowedEventTypes(), true)) {
            $errors[] = 'event_type is invalid';
            return ['valid' => false, 'errors' => $errors];
        }

        switch ($eventType) {
            case self::EVENT_SEARCH_QUERY:
                if (!$this->isNonEmptyString($metadata['query'] ?? null)) {
                    $errors[] = 'metadata.query is required';
                }
                if (isset($metadata['filters']) && !is_array($metadata['filters'])) {
                    $errors[] = 'metadata.filters must be object/array';
                }
                if (isset($metadata['result_count']) && !is_numeric($metadata['result_count'])) {
                    $errors[] = 'metadata.result_count must be numeric';
                }
                break;

            case self::EVENT_CLICK_RESULT:
                if (!$this->isNonEmptyString($metadata['query'] ?? null)) {
                    $errors[] = 'metadata.query is required';
                }
                if (!$this->isNonEmptyString($metadata['product_id'] ?? null)) {
                    $errors[] = 'metadata.product_id is required';
                }
                if (!isset($metadata['position']) || !is_numeric($metadata['position'])) {
                    $errors[] = 'metadata.position is required and must be numeric';
                }
                if (isset($metadata['page']) && !is_numeric($metadata['page'])) {
                    $errors[] = 'metadata.page must be numeric';
                }
                break;

            case self::EVENT_PRODUCT_VIEW:
                if (!$this->isNonEmptyString($metadata['product_id'] ?? null)) {
                    $errors[] = 'metadata.product_id is required';
                }
                break;

            case self::EVENT_ADD_TO_CART:
                if (!$this->isNonEmptyString($metadata['product_id'] ?? null)) {
                    $errors[] = 'metadata.product_id is required';
                }
                if (!isset($metadata['quantity']) || !is_numeric($metadata['quantity'])) {
                    $errors[] = 'metadata.quantity is required and must be numeric';
                }
                if (isset($metadata['price']) && !is_numeric($metadata['price'])) {
                    $errors[] = 'metadata.price must be numeric';
                }
                break;

            case self::EVENT_WISHLIST_ADD:
                if (!$this->isNonEmptyString($metadata['product_id'] ?? null)) {
                    $errors[] = 'metadata.product_id is required';
                }
                break;

            case self::EVENT_PURCHASE:
                if (!$this->isNonEmptyString($metadata['order_id'] ?? null)) {
                    $errors[] = 'metadata.order_id is required';
                }
                $items = $metadata['items'] ?? null;
                if (!is_array($items) || empty($items)) {
                    $errors[] = 'metadata.items is required and must be non-empty array';
                    break;
                }
                foreach ($items as $index => $item) {
                    if (!is_array($item)) {
                        $errors[] = 'metadata.items.' . $index . ' must be object';
                        continue;
                    }
                    if (!$this->isNonEmptyString($item['product_id'] ?? null)) {
                        $errors[] = 'metadata.items.' . $index . '.product_id is required';
                    }
                    if (!isset($item['quantity']) || !is_numeric($item['quantity'])) {
                        $errors[] = 'metadata.items.' . $index . '.quantity is required and must be numeric';
                    }
                    if (!isset($item['price']) || !is_numeric($item['price'])) {
                        $errors[] = 'metadata.items.' . $index . '.price is required and must be numeric';
                    }
                }
                if (isset($metadata['total_amount']) && !is_numeric($metadata['total_amount'])) {
                    $errors[] = 'metadata.total_amount must be numeric';
                }
                break;
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    private function isNonEmptyString(mixed $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }
}

