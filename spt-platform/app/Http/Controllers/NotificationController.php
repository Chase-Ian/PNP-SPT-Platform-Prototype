<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->notifications()
            ->latest()
            ->take(10)
            ->get()
            ->map(fn ($n) => [
                'id' => $n->id,
                'message' => $n->data['message'] ?? '',
                'url' => $this->urlFor($n->data),
                'read' => $n->read_at !== null,
                'created_at' => $n->created_at->diffForHumans(),
            ]);
    }

    public function markRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        // The bell calls this with axios: no need to render a page back.
        return $request->expectsJson() ? response()->noContent() : back();
    }

    /**
     * Where clicking the notification takes the user. New notifications carry
     * a 'url'; older ones only have a message, so they're linked by topic.
     */
    private function urlFor(array $data): ?string
    {
        if (! empty($data['url'])) {
            return $data['url'];
        }

        $message = strtolower($data['message'] ?? '');

        return match (true) {
            str_contains($message, 'certificate'), str_contains($message, 'completed') => route('certificates.index', absolute: false),
            str_contains($message, 'course') => route('courses.index', absolute: false),
            str_contains($message, 'email'), str_contains($message, 'two-factor') => route('profile.edit', absolute: false),
            default => null,
        };
    }
}
