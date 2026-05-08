<?php

namespace LARAVEL\Controllers\Web;

use Illuminate\Http\Request;
use LARAVEL\Controllers\Controller;
use LARAVEL\Services\EventTrackingService;

class EventTrackingController extends Controller
{
    public function track(Request $request)
    {
        $rawPayload = [];
        try {
            $rawContent = (string) $request->getContent();
            if (trim($rawContent) !== '') {
                $decoded = json_decode($rawContent, true);
                if (is_array($decoded)) {
                    $rawPayload = $decoded;
                }
            }
        } catch (\Throwable $e) {
            $rawPayload = [];
        }

        $pick = function (string $key, mixed $default = null) use ($request, $rawPayload) {
            if (array_key_exists($key, $rawPayload)) {
                return $rawPayload[$key];
            }
            return $request->input($key, $default);
        };

        $payload = [
            'event_id' => $pick('event_id', ''),
            'event_type' => $pick('event_type', ''),
            'user_id' => $pick('user_id', ''),
            'anonymous_id' => $pick('anonymous_id', ''),
            'session_id' => $pick('session_id', ''),
            'timestamp' => $pick('timestamp', ''),
            'source' => $pick('source', 'web'),
            'metadata' => $pick('metadata', []),
            'csrf_token' => $pick('csrf_token', ''),
            '_token' => $pick('_token', ''),
        ];

        $tracker = new EventTrackingService();
        $result = $tracker->trackFromRequest($payload, $request);

        if (!empty($result['success'])) {
            return response()->json([
                'success' => true,
                'event_id' => $result['event_id'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid tracking payload',
            'errors' => $result['errors'] ?? [],
        ], 422);
    }
}
