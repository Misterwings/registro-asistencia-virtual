<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Services\AttendanceService;
use App\Services\EventService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

        $request->merge([
            'id_number' => $this->attendanceService->normalizeIdNumber($request->input('id_number')),
        ]);

        $validated = $request->validate([
            'first_names' => ['required', 'string', 'max:255'],
            'last_names' => ['required', 'string', 'max:255'],
            'id_number' => ['required', 'string', 'max:50', 'regex:/\A[0-9]+\z/'],
            'position_id' => [
                'required',
                'integer',
                Rule::exists('positions', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'headquarter_id' => [
                'required',
                'integer',
                Rule::exists('headquarters', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'signature' => ['required', 'string'],
        ], [
            'id_number.regex' => 'El número de identificación solo puede contener números.',
        ]);

        if ($this->attendanceService->isAlreadyRegistered($event, $validated['id_number'])) {
            return back()->withErrors([
                'id_number' => 'Ya has registrado tu asistencia a este evento anteriormente.',
            ])->withInput();
        }

        try {
            $this->attendanceService->register($validated, $event);
        } catch (QueryException $exception) {
            $errorCode = (int) ($exception->errorInfo[1] ?? 0);
            $isUniqueViolation = in_array($errorCode, [19, 1062], true)
                || str_contains(strtolower($exception->getMessage()), 'unique');

            if (! $isUniqueViolation) {
                throw $exception;
            }

            return back()->withErrors([
                'id_number' => 'Ya has registrado tu asistencia a este evento anteriormente.',
            ])->withInput();
        }

        return back()->with('success', '¡Asistencia registrada exitosamente!');
    }

    private function expiredResponse(Event $event)
    {
        return response()->view('public.event-expired', compact('event'), 410);
    }
}
