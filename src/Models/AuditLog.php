<?php

declare(strict_types=1);

namespace Deyvo\Core\Models;

use Illuminate\Database\Eloquent\Model;

final class AuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $table = 'deyvo_audit_logs';

    protected $fillable = [
        'event',
        'subject_type',
        'subject_id',
        'subject_label',
        'actor_id',
        'actor_name',
        'actor_email',
        'request_id',
        'method',
        'path',
        'ip_address',
        'context',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function actorLabel(): string
    {
        return $this->actor_name ?? $this->actor_email ?? 'Onbekende gebruiker';
    }

    public function eventLabel(): string
    {
        return self::labelFor($this->event);
    }

    public static function labelFor(string $event): string
    {
        return match ($event) {
            'content.created' => 'Content aangemaakt',
            'content.updated' => 'Content bijgewerkt',
            'content.deleted' => 'Content verwijderd',
            'setting.created' => 'Instelling aangemaakt',
            'setting.updated' => 'Instelling bijgewerkt',
            'setting.deleted' => 'Instelling verwijderd',
            'custom.updated' => 'Dashboardonderdeel bijgewerkt',
            'layout.updated' => 'Layoutonderdeel bijgewerkt',
            'page.created' => 'Pagina aangemaakt',
            'page.updated' => 'Pagina bijgewerkt',
            'page.published' => 'Pagina gepubliceerd',
            'page.restored' => 'Revisie hersteld',
            'page.field_updated' => 'Paginaveld bijgewerkt',
            'page.preview_started' => 'Preview gestart',
            'page.preview_stopped' => 'Preview gestopt',
            'page.create_failed' => 'Pagina aanmaken mislukt',
            'page.update_failed' => 'Pagina bijwerken mislukt',
            'page.publish_failed' => 'Pagina publiceren mislukt',
            'page.field_update_failed' => 'Paginaveld bijwerken mislukt',
            'dashboard.request_failed' => 'Dashboardfout',
            default => str_replace(['.', '_'], [' ', ' '], $event),
        };
    }
}
