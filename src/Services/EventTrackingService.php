<?php

namespace LARAVEL\Services;

use Illuminate\Http\Request;
use LARAVEL\Models\UserEventModel;

class EventTrackingService
{
    public function __construct(
        protected ?EventSchemaValidator $validator = null
    ) {
        $this->validator = $this->validator ?: new EventSchemaValidator();
    }

    /**
     * @param array<string,mixed> $payload
     * @return array{success:bool,event_id:?string,errors:array<int,string>}
     */
    public function track(array $payload): array
    {
        try {
            $eventType = trim((string) ($payload['event_type'] ?? ''));
            $metadata = $payload['metadata'] ?? [];
            if (!is_array($metadata)) {
                return ['success' => false, 'event_id' => null, 'errors' => ['metadata must be object/array']];
            }

            $validation = $this->validator->validate($eventType, $metadata);
            if (empty($validation['valid'])) {
                return ['success' => false, 'event_id' => null, 'errors' => $validation['errors'] ?? ['invalid payload']];
            }

            $eventId = $this->normalizeEventId($payload['event_id'] ?? null);
            $createdAt = $this->normalizeTimestamp($payload['timestamp'] ?? null);

            $record = [
                'event_id' => $eventId,
                'event_type' => $eventType,
                'user_id' => $this->normalizeNullableString($payload['user_id'] ?? null),
                'anonymous_id' => $this->normalizeNullableString($payload['anonymous_id'] ?? null),
                'session_id' => $this->normalizeSessionId($payload['session_id'] ?? null),
                'source' => $this->normalizeSource($payload['source'] ?? null),
                'metadata' => $metadata,
                'created_at' => $createdAt,
            ];

            UserEventModel::create($record);
            $this->maybeCleanupExpiredEvents();

            return ['success' => true, 'event_id' => $eventId, 'errors' => []];
        } catch (\Throwable $e) {
            error_log('[EventTrackingService] ' . $e->getMessage());
            return ['success' => false, 'event_id' => null, 'errors' => ['track_failed']];
        }
    }

    /**
     * @param array<string,mixed> $payload
     * @return array{success:bool,event_id:?string,errors:array<int,string>}
     */
    public function trackFromRequest(array $payload, ?Request $request = null): array
    {
        $request = $request ?: request();

        if (!isset($payload['session_id']) || trim((string) $payload['session_id']) === '') {
            $payload['session_id'] = $this->resolveSessionId($request);
        }
        if (!isset($payload['source']) || trim((string) $payload['source']) === '') {
            $payload['source'] = 'web';
        }
        if (!isset($payload['user_id']) || trim((string) $payload['user_id']) === '') {
            $member = session()->get('member');
            if (is_array($member)) {
                $member = $member['member'] ?? null;
            }
            if (!empty($member)) {
                $payload['user_id'] = (string) $member;
            }
        }

        return $this->track($payload);
    }

    private function resolveSessionId(?Request $request): string
    {
        $sessionId = '';
        if ($request) {
            $sessionId = trim((string) ($request->input('session_id', '') ?: $request->header('X-Session-Id', '')));
        }
        if ($sessionId === '') {
            $sessionId = (string) session_id();
        }
        if ($sessionId === '') {
            $sessionId = $this->generateEventId('sess_');
        }
        return $sessionId;
    }

    private function normalizeEventId(mixed $value): string
    {
        $id = trim((string) $value);
        if ($id !== '') {
            return $id;
        }
        return $this->generateEventId('evt_');
    }

    private function generateEventId(string $prefix): string
    {
        try {
            return $prefix . bin2hex(random_bytes(16));
        } catch (\Throwable $e) {
            return $prefix . str_replace('.', '', uniqid('', true));
        }
    }

    private function normalizeTimestamp(mixed $value): string
    {
        if (is_string($value) && trim($value) !== '') {
            $time = strtotime($value);
            if ($time !== false) {
                return date('Y-m-d H:i:s', $time);
            }
        }
        return date('Y-m-d H:i:s');
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $str = trim((string) $value);
        return $str !== '' ? $str : null;
    }

    private function normalizeSessionId(mixed $value): string
    {
        $sessionId = trim((string) $value);
        if ($sessionId === '') {
            $sessionId = $this->generateEventId('sess_');
        }
        return $sessionId;
    }

    private function normalizeSource(mixed $value): string
    {
        $source = strtolower(trim((string) $value));
        if (!in_array($source, ['web', 'app', 'api'], true)) {
            $source = 'web';
        }
        return $source;
    }

    private function maybeCleanupExpiredEvents(): void
    {
        try {
            $enabled = (bool) config('event_tracking.cleanup.enabled', true);
            if (!$enabled) {
                return;
            }

            $chancePercent = (int) config('event_tracking.cleanup.chance_percent', 2);
            $chancePercent = max(0, min(100, $chancePercent));
            if ($chancePercent <= 0) {
                return;
            }

            $random = random_int(1, 100);
            if ($random > $chancePercent) {
                return;
            }

            $batchSize = max(100, (int) config('event_tracking.cleanup.batch_size', 1000));
            $retentionDays = (array) config('event_tracking.retention_days', []);
            if (empty($retentionDays)) {
                return;
            }

            foreach ($retentionDays as $eventType => $days) {
                $days = max(1, (int) $days);
                $cutoff = date('Y-m-d H:i:s', strtotime('-' . $days . ' days'));
                $ids = UserEventModel::query()
                    ->where('event_type', (string) $eventType)
                    ->where('created_at', '<', $cutoff)
                    ->orderBy('id', 'asc')
                    ->limit($batchSize)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->filter(fn ($id) => $id > 0)
                    ->values()
                    ->all();

                if (!empty($ids)) {
                    UserEventModel::query()->whereIn('id', $ids)->delete();
                }
            }
        } catch (\Throwable $e) {
            error_log('[EventTrackingService][cleanup] ' . $e->getMessage());
        }
    }
}
