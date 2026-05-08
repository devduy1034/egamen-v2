<?php

$testModeRaw = env('VNPAY_TEST_MODE', true);
if (is_string($testModeRaw)) {
    $testModeRaw = strtolower(trim($testModeRaw));
    $testMode = in_array($testModeRaw, ['1', 'true', 'yes', 'on'], true);
} else {
    $testMode = (bool) $testModeRaw;
}

return [
    'defaults' => [],
    'gateway' => env('PAYMENT_GATEWAY_DEFAULT', 'VNPay'),
    'gateways' => [
        'VNPay' => [
            'vnp_Version' => env('VNPAY_VERSION', '2.1.0'),
            'options' => [
                'vnp_TmnCode' => trim((string) env('VNPAY_TMN_CODE', 'DQEPEATK')),
                'vnp_HashSecret' => trim((string) env('VNPAY_HASH_SECRET', 'KET4ZAOE3V0FR53AY816EP46OQ47SC6G')),
                'vnp_SecureHashType' => strtolower(trim((string) env('VNPAY_HASH_TYPE', 'sha512'))),
                'testMode' => $testMode,
            ],
        ],
    ],
];
