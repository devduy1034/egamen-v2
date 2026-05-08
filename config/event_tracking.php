<?php

return [
    'cleanup' => [
        'enabled' => (bool) env('EVENT_CLEANUP_ENABLED', true),
        'chance_percent' => max(0, min(100, (int) env('EVENT_CLEANUP_CHANCE_PERCENT', 2))),
        'batch_size' => max(100, (int) env('EVENT_CLEANUP_BATCH_SIZE', 1000)),
    ],
    'retention_days' => [
        'search_query' => max(1, (int) env('EVENT_RETENTION_SEARCH_QUERY_DAYS', 90)),
        'click_result' => max(1, (int) env('EVENT_RETENTION_CLICK_RESULT_DAYS', 90)),
        'product_view' => max(1, (int) env('EVENT_RETENTION_PRODUCT_VIEW_DAYS', 90)),
        'add_to_cart' => max(1, (int) env('EVENT_RETENTION_ADD_TO_CART_DAYS', 120)),
        'wishlist_add' => max(1, (int) env('EVENT_RETENTION_WISHLIST_ADD_DAYS', 120)),
        'purchase' => max(1, (int) env('EVENT_RETENTION_PURCHASE_DAYS', 365)),
    ],
];
