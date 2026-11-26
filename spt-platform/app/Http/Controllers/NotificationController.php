<?php

namespace App\Http\Controllers;

use App\Models\User;
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
                'url' => $this->urlFor($n->data, $request->user()),
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
    private function urlFor(array $data, User $user): ?string
    {
        $url = $data['url'] ?? null;

        if (empty($url)) {
            $message = strtolower($data['message'] ?? '');
            $url = match (true) {
                str_contains($message, 'certificate'), str_contains($message, 'completed') => route('certificates.index', absolute: false),
                str_contains($message, 'course') => route('courses.index', ['tab' => 'mine'], false),
                str_contains($message, 'two-factor'), str_contains($message, 'mfa') => route('profile.edit', absolute: false).'#mfa',
                str_contains($message, 'email') => route('profile.edit', absolute: false),
                default => null,
            };
        }

        return $url ? $this->workingLink($url, $user) : null;
    }

    /**
     * Saved links can go stale: a page that was removed, a page this account's
     * role can't open, or a full address from another copy of the site (they
     * share a database). Returns the link as a path on this site when it still
     * works for this user, otherwise null (the bell then only marks it read).
     */
    private function workingLink(string $url, User $user): ?string
    {
        $parts = parse_url($url) ?: [];
        $path = '/'.ltrim($parts['path'] ?? '', '/');

        try {
            $route = app('router')->getRoutes()->match(Request::create($path, 'GET'));
        } catch (\Throwable) {
            return null;
        }

        foreach ($route->gatherMiddleware() as $middleware) {
            if (is_string($middleware) && str_starts_with($middleware, 'role:')
                && ! in_array($user->role, explode(',', substr($middleware, 5)), true)) {
                return null;
            }
        }

        return $path
            .(isset($parts['query']) ? '?'.$parts['query'] : '')
            .(isset($parts['fragment']) ? '#'.$parts['fragment'] : '');
    }
}
