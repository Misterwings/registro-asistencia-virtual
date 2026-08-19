<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\AttendanceService;
use App\Services\EventService;
use Illuminate\Http\Request;

class PublicAttendanceController extends Controller
{
    public function __construct(
        protected EventService $eventService,
        protected AttendanceService $attendanceService,
    ) {}

    public function show(string $slug)
    {
        $event = $this->eventService->findBySlug($slug);

        if (!$event) {
            abort(404);
        }

        if (!$this->eventService->isPublicLinkAvailable($event)) {
            return $this->expiredResponse($event);
        }

        $positions = \App\Models\Position::where('is_active', true)->orderBy('name')->get();
        $headquarters = \App\Models\Headquarter::where('is_active', true)->orderBy('name')->get();

        $attachmentUrl = $this->eventService->getAttachmentUrl($event);
        $isViewable = $this->eventService->isAttachmentViewable($event);

        return view('public.event-register', compact(
            'event', 'positions', 'headquarters',
            'attachmentUrl', 'isViewable'
        ));
    }

    public function store(Request $request, string $slug)
    {
        $event = $this->eventService->findBySlug($slug);

        if (!$event) {
            abort(404);
        }

        if (!$this->eventService->isPublicLinkAvailable($event)) {
            return $this->expiredResponse($event);
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'id_number' => ['required', 'string', 'max:50'],
            'position' => ['nullable', 'string', 'max:255'],
            'headquarter' => ['nullable', 'string', 'max:255'],
            'signature' => ['required', 'string'],
        ]);

        if ($this->attendanceService->isAlreadyRegistered($event, $validated['id_number'])) {
            return back()->withErrors([
                'id_number' => 'Ya has registrado tu asistencia a este evento anteriormente.',
            ])->withInput();
        }

        $this->attendanceService->register($validated, $event);

        return back()->with('success', '¡Asistencia registrada exitosamente!');
    }

    private function expiredResponse(Event $event)
    {
        return response()->view('public.event-expired', compact('event'), 410);
    }
}
