<?php

declare(strict_types=1);

namespace Deyvo\Core\Http\Controllers\Dashboard;

use Deyvo\Core\Support\AuditLogger;
use Deyvo\Core\Support\Flash;
use Deyvo\Core\Support\SiteSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class SeoController
{
    public function __construct(
        private AuditLogger $audit,
    ) {
    }

    public function index(): View
    {
        return view('deyvo::dashboard.seo.index', [
            'seo' => [
                'title' => SiteSettings::get('seo.title'),
                'description' => SiteSettings::get('seo.description'),
                'indexable' => SiteSettings::get('seo.indexable', true),
                'canonical_url' => SiteSettings::get('seo.canonical_url'),
                'og_image' => SiteSettings::get('seo.og_image'),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = Validator::validate($request->all(), [
            'seo.title' => ['nullable', 'string', 'max:160'],
            'seo.description' => ['nullable', 'string', 'max:500'],
            'seo.canonical_url' => ['nullable', 'url', 'max:2048'],
            'seo.og_image' => ['nullable', 'url', 'max:2048'],
            'seo.indexable' => ['nullable', 'boolean'],
        ]);

        SiteSettings::put('seo.title', data_get($validated, 'seo.title'), [
            'label' => 'Standaard paginatitel',
            'group' => 'SEO',
            'type' => 'text',
        ]);
        SiteSettings::put('seo.description', data_get($validated, 'seo.description'), [
            'label' => 'Standaard metabeschrijving',
            'group' => 'SEO',
            'type' => 'textarea',
        ]);
        SiteSettings::put('seo.canonical_url', data_get($validated, 'seo.canonical_url'), [
            'label' => 'Canonieke URL',
            'group' => 'SEO',
            'type' => 'url',
        ]);
        SiteSettings::put('seo.og_image', data_get($validated, 'seo.og_image'), [
            'label' => 'Social image',
            'group' => 'SEO',
            'type' => 'url',
        ]);
        SiteSettings::put('seo.indexable', $request->boolean('seo.indexable'), [
            'label' => 'Indexeer website',
            'group' => 'SEO',
            'type' => 'boolean',
        ]);

        $this->audit->record('seo.updated', null, [
            'subject_label' => 'SEO',
            'fields' => array_keys($validated['seo'] ?? []),
        ]);
        Flash::success('SEO-instellingen zijn bijgewerkt.');

        return redirect()->route('deyvo.dashboard.seo.index');
    }
}
