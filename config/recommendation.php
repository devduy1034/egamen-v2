<?php

return [
    'event_weights' => [
        'product_view' => 1,
        'click_result' => 2,
        'wishlist_add' => 4,
        'add_to_cart' => 5,
        'purchase' => 8,
    ],
    'max_event_lookback' => (int) env('RECOMMENDATION_EVENT_LOOKBACK', 300),
    'candidate_limit' => (int) env('RECOMMENDATION_CANDIDATE_LIMIT', 240),
    'default_limit' => (int) env('RECOMMENDATION_DEFAULT_LIMIT', 20),
    'max_limit' => (int) env('RECOMMENDATION_MAX_LIMIT', 60),
];

