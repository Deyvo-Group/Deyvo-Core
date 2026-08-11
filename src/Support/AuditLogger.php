<?php

declare(strict_types=1);

namespace Deyvo\Core\Support;

use Deyvo\Core\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Throwable;

final class AuditLogger
{
    public function __construct(
        private Actor $actor,
    ) {
    }

    public function record(string $event, ?Model $subject = null, array $context = []): void
    {
        if (! config('deyvo-core.audit.enabled', true)) {
            return;
        }

        try {
            $request = app()->bound('request') ? app('request') : null;
            $actor = $this->actor->current();
            $subjectLabel = $this->subjectLabel($subject, $context);
            unset($context['subject_label']);

            AuditLog::query()->create([
                'event' => $event,
                'subject_type' => $subject?->getMorphClass(),
                'subject_id' => $subject?->getKey() === null ? null : (string) $subject->getKey(),
                'subject_label' => $subjectLabel,
                'actor_id' => $actor['id'],
                'actor_name' => $actor['name'],
                'actor_email' => $actor['email'],
                'request_id' => $request instanceof Request ? $request->attributes->get('deyvo.request_id') : null,
                'method' => $request instanceof Request ? $request->method() : null,
                'path' => $request instanceof Request ? $request->path() : null,
                'ip_address' => $request instanceof Request ? $request->ip() : null,
                'context' => $context === [] ? null : $context,
            ]);
        } catch (Throwable) {
        }
    }

    private function subjectLabel(?Model $subject, array $context): ?string
    {
        $label = $context['subject_label'] ?? null;

        if (! is_scalar($label) && $subject instanceof Model) {
            $label = $subject->getAttribute('key')
                ?? $subject->getAttribute('title')
                ?? $subject->getAttribute('name');
        }

        return is_scalar($label) && (string) $label !== '' ? mb_substr((string) $label, 0, 255) : null;
    }
}
