<?php

namespace App\Services;

use App\Traits\ZoomJWT;
use Illuminate\Support\Facades\Validator;

/**
 * Creates Zoom meetings through the Zoom API.
 *
 * This used to be reached by the application making an unauthenticated HTTP request
 * to its own /api/meetings route. Calling the Zoom API directly means that route no
 * longer has to be open to the internet.
 */
class ZoomMeetingService
{
    use ZoomJWT;

    public const MEETING_TYPE_INSTANT = 1;
    public const MEETING_TYPE_SCHEDULE = 2;
    public const MEETING_TYPE_RECURRING = 3;
    public const MEETING_TYPE_FIXED_RECURRING_FIXED = 8;

    /**
     * Schedule a meeting.
     *
     * @param  array  $data  topic, start_time, and optionally agenda and co-host
     * @return array{success: bool, data: mixed}
     */
    public function schedule(array $data)
    {
        $validator = Validator::make($data, [
            'topic' => 'required|string',
            'start_time' => 'required|date',
            'agenda' => 'string|nullable',
            'co-host' => 'string|nullable',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'data' => $validator->errors(),
            ];
        }

        $validated = $validator->validated();

        $response = $this->zoomPost('users/me/meetings', [
            'topic' => $validated['topic'],
            'type' => self::MEETING_TYPE_SCHEDULE,
            'start_time' => $this->toZoomTimeFormat($validated['start_time']),
            'timezone' => config('app.timezone'),
            'duration' => 30,
            'agenda' => $validated['agenda'] ?? '',
            'settings' => [
                'host_video' => false,
                'participant_video' => false,
                'waiting_room' => false,
                'alternative_hosts' => $validated['co-host'] ?? '',
                'auto_recording' => 'cloud',
            ],
        ]);

        return [
            'success' => $response->status() === 201,
            'data' => json_decode($response->body(), true),
        ];
    }
}
