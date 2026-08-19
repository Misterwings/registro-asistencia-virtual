<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Str;

class EventService
{
    public function generateSlug(string $topic): string
    {
        $base = Str::slug($topic) . '-' . now()->format('YmdHi');
        $slug = $base;
        $counter = 1;

        while (Event::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function findBySlug(string $slug): ?Event
    {
        return Event::where('slug', $slug)->first();
    }

    public function isPublicLinkAvailable(Event $event): bool
    {
        if (! $event->has_expiration) {
            return true;
        }

        if (! $event->expiration_date) {
            return false;
        }

        return now('America/Bogota')->toDateString() <= $event->expiration_date->toDateString();
    }

    public function getAttachmentUrl(Event $event): ?string
    {
        if (!$event->attachment_path) {
            return null;
        }

        return asset('storage/' . $event->attachment_path);
    }

    public function isAttachmentViewable(Event $event): bool
    {
        if (!$event->attachment_path) {
            return false;
        }

        $extension = strtolower(pathinfo($event->attachment_path, PATHINFO_EXTENSION));
        return in_array($extension, ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'webp']);
    }
}
