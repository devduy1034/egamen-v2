<?php

return [
    'enabled' => env('AI_SEARCH_ENABLED', true),
    'search_per_page' => (int) env('AI_SEARCH_PER_PAGE', 36),
    'search_max_per_page' => (int) env('AI_SEARCH_MAX_PER_PAGE', 72),
    'search_result_limit' => (int) env('AI_SEARCH_RESULT_LIMIT', 180),
    'embedding_model' => env('AI_SEARCH_EMBEDDING_MODEL', 'text-embedding-3-small'),
    'embedding_dimensions' => (int) env('AI_SEARCH_EMBEDDING_DIMENSIONS', 384),
    'semantic_result_limit' => (int) env('AI_SEARCH_SEMANTIC_RESULT_LIMIT', 60),
    'semantic_weight' => (int) env('AI_SEARCH_SEMANTIC_WEIGHT', 180),
    'min_semantic_score' => (float) env('AI_SEARCH_MIN_SEMANTIC_SCORE', 0.18),
    'bootstrap_batch_size' => (int) env('AI_SEARCH_BOOTSTRAP_BATCH_SIZE', 120),
    'sync_batch_size' => (int) env('AI_SEARCH_SYNC_BATCH_SIZE', 16),
    'embedding_request_batch_size' => (int) env('AI_SEARCH_EMBEDDING_REQUEST_BATCH_SIZE', 24),
    'http_timeout' => (int) env('AI_SEARCH_HTTP_TIMEOUT', 45),
];
