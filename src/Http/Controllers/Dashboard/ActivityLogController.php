<?php

declare(strict_types=1);

namespace Deyvo\Core\Http\Controllers\Dashboard;

use Deyvo\Core\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class ActivityLogController
{
    public function index(Request $request): View
    {
        abort_unless(config('deyvo-core.audit.enabled', true), 404);

        $event = trim((string) $request->query('event', ''));
        $search = trim((string) $request->query('q', ''));
        $activities = AuditLog::query()
            ->when($event !== '', static fn ($query) => $query->where('event', $event))
            ->when($search !== '', static function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('actor_name', 'like', "%{$search}%")
                        ->orWhere('actor_email', 'like', "%{$search}%")
                        ->orWhere('subject_label', 'like', "%{$search}%")
                        ->orWhere('request_id', 'like', "%{$search}%");
                });
            })
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('deyvo::dashboard.activity.index', [
            'activities' => $activities,
            'events' => AuditLog::query()->distinct()->orderBy('event')->pluck('event'),
            'event' => $event,
            'search' => $search,
        ]);
    }

    public function show(AuditLog $activity): View
    {
        abort_unless(config('deyvo-core.audit.enabled', true), 404);

        return view('deyvo::dashboard.activity.show', [
            'activity' => $activity,
        ]);
    }
}
