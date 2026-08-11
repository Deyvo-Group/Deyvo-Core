# Deyvo Core Dashboard Contract

Dit document beschrijft welke waarden een Laravel-website aan Deyvo Core kan geven en welke opslag, routes en rendering daaruit volgen.

## Inschakelen

Publiceer eerst de packageconfiguratie en het JSON-startbestand.

~~~bash
php artisan vendor:publish --tag=deyvo-config
php artisan vendor:publish --tag=deyvo-dashboard-schema
php artisan migrate
~~~

Gebruik vervolgens deze configuratie in config/deyvo-core.php.

~~~php
'dashboard' => [
    'enabled' => true,
    'path' => 'dashboard',
    'middleware' => ['web', 'auth'],
    'schema' => [
        'path' => 'resources/deyvo/dashboard.json',
    ],
    'pages' => [
        'enabled' => true,
    ],
],
~~~

| Waarde | Verwachting | Resultaat |
| --- | --- | --- |
| enabled | Boolean | Laadt het dashboard en de package-migraties. |
| path | Pad zonder begin- of eindslash | Dashboardbasis, bijvoorbeeld /dashboard. |
| middleware | Array met hostmiddleware | Beveiligt dashboard, preview en editor. |
| schema.path | Absoluut pad of pad relatief aan de hostroot | Core leest dit JSON-bestand lokaal. |
| pages.enabled | Boolean | Activeert pagina’s, revisies, preview en editorroutes. |

Core levert geen login, users, rollen, permissions, uploads of mediafunctionaliteit. De website levert de middleware die toegang tot het dashboard geeft.

## Dashboard JSON

Het JSON-bestand heeft twee onafhankelijke onderdelen.

~~~json
{
  "pages": [],
  "templates": []
}
~~~

pages definieert gegroepeerde algemene instellingen en content. Templates definieert pagina’s met versieerbare secties.

## Algemene instellingen

Een item in pages verschijnt als een extra dashboardonderdeel.

~~~json
{
  "key": "algemeen",
  "label": "Algemeen",
  "description": "Beheer contactgegevens.",
  "sort": 40,
  "fields": [
    {
      "key": "contact.email",
      "label": "E-mailadres",
      "type": "email",
      "required": true
    },
    {
      "key": "homepage.intro",
      "label": "Introductie",
      "type": "textarea",
      "storage": "content",
      "content_title": "Homepage introductie",
      "published": true
    }
  ]
}
~~~

| Veld | Verwachting | Standaard | Resultaat |
| --- | --- | --- | --- |
| key | Kleine letters, cijfers, punten, underscores en koppeltekens | Verplicht | Opslagsleutel. |
| label | Niet-lege tekst | Verplicht | Label in dashboard en formulier. |
| description | Tekst | null | Uitleg onder de titel. |
| sort | Integer | 100 | Positie in dashboardnavigatie. |
| fields | Niet-lege array | Verplicht | Bewerkbare waarden. |

### Instellingsveld

| Veld | Verwachting | Standaard | Resultaat |
| --- | --- | --- | --- |
| key | Kleine letters, cijfers, punten, underscores en koppeltekens | Verplicht | Sleutel in deyvo_settings of deyvo_contents. |
| label | Niet-lege tekst | Verplicht | Formulierlabel. |
| type | text, textarea, email, url, select of boolean | text | Invoer en validatie. |
| storage | setting of content | setting | Doeltabel. |
| required | Boolean | false | Vereist een waarde. |
| help | Tekst | null | Hulptekst onder het veld. |
| placeholder | Tekst | null | Placeholder voor tekstvelden. |
| options | Array met value en label | Verplicht voor select | Toegestane selectwaarden. |
| content_title | Tekst | Waarde van label | Titel voor een contentrecord. |
| published | Boolean | true | Publicatiestatus van een contentrecord. |

Bij storage setting schrijft Core naar deyvo_settings.

~~~php
use Deyvo\Core\Support\SiteSettings;

$email = SiteSettings::get('contact.email');
~~~

Bij storage content schrijft Core naar deyvo_contents.

~~~php
use Deyvo\Core\Support\SiteContent;

$intro = SiteContent::body('homepage.intro');
~~~

SiteContent::body geeft alleen gepubliceerde content terug. Beide helpers geven de opgegeven fallback terug wanneer de sleutel nog niet bestaat.

## Pagina-templates

Een template definieert de bewerkbare secties van een pagina. Maak een template voordat een beheerder een pagina aanmaakt.

~~~json
{
  "key": "landing",
  "label": "Landingspagina",
  "description": "Standaardpagina met hero.",
  "sort": 10,
  "sections": [
    {
      "key": "hero",
      "label": "Hero",
      "description": "Eerste zichtbare sectie.",
      "fields": [
        {
          "key": "title",
          "label": "Titel",
          "type": "text",
          "required": true
        },
        {
          "key": "intro",
          "label": "Introductie",
          "type": "textarea"
        }
      ]
    }
  ]
}
~~~

| Templateveld | Verwachting | Resultaat |
| --- | --- | --- |
| key | Kleine letters, cijfers en koppeltekens | Stabiele templatesleutel. |
| label | Niet-lege tekst | Keuze in het paginaformulier. |
| description | Tekst | Uitleg bij template of sectie. |
| sort | Integer | Volgorde van templates. |
| sections | Niet-lege array | Duidelijke groepen in het paginaformulier. |
| sections[].key | Kleine letters, cijfers, underscores en koppeltekens | Sleutel in de revisie-JSON. |
| sections[].fields | Niet-lege array | Bewerkbare paginawaarden. |

Templatevelden ondersteunen text, textarea, email, url, select en boolean. Dezelfde validatieregels voor required, help, placeholder en options gelden als bij algemene instellingen.

## Pagina- en revisieopslag

Wanneer een beheerder een pagina aanmaakt, geeft het dashboard deze waarden aan Core.

~~~json
{
  "title": "Homepage",
  "slug": "home",
  "template": "landing",
  "sections": {
    "hero": {
      "title": "Welkom",
      "intro": "Welkom bij Deyvo."
    }
  },
  "seo": {
    "title": "Deyvo",
    "description": "Digitale ervaringen.",
    "indexable": true
  }
}
~~~

| Invoer | Core-opslag |
| --- | --- |
| slug bij aanmaken | Stabiele deyvo_pages.key. |
| title, slug, template, sections, seo | Nieuwe of bijgewerkte deyvo_page_revisions-revisie. |
| Publiceren | deyvo_pages.published_revision_id en published_slug verwijzen naar de live-revisie. |
| Bewerken van een live pagina | Core kloont eerst de live-revisie naar een nieuw concept. |
| Revisie herstellen | Core maakt een nieuw concept op basis van de gekozen revisie. |

Een bezoeker leest altijd published_revision_id. Een actieve dashboard-preview leest draft_revision_id. Daardoor kan conceptinhoud niet per ongeluk live verschijnen.

## Dashboardroutes

Alle routes gebruiken dashboard.path en dashboard.middleware.

| Methode | Route | Naam | Resultaat |
| --- | --- | --- | --- |
| GET | /dashboard/pages | deyvo.dashboard.pages.index | Pagina-overzicht. |
| GET | /dashboard/pages/create | deyvo.dashboard.pages.create | Formulier voor nieuw concept. |
| POST | /dashboard/pages | deyvo.dashboard.pages.store | Maakt pagina en eerste conceptrevisie. |
| GET | /dashboard/pages/{page}/edit | deyvo.dashboard.pages.edit | Secties en SEO bewerken. |
| PUT | /dashboard/pages/{page} | deyvo.dashboard.pages.update | Slaat concept op. |
| POST | /dashboard/pages/{page}/publish | deyvo.dashboard.pages.publish | Publiceert huidig concept. |
| GET | /dashboard/pages/{page}/preview | deyvo.dashboard.pages.preview | Start previewsessie en leidt naar de hostlayout. |
| PATCH | /dashboard/pages/{page}/fields | deyvo.dashboard.pages.fields.update | Autosave voor live editor. |
| GET | /dashboard/pages/{page}/revisions | deyvo.dashboard.pages.revisions | Revisiehistorie. |
| POST | /dashboard/pages/{page}/revisions/{revision}/restore | deyvo.dashboard.pages.revisions.restore | Herstelt revisie als nieuw concept. |

## Editor-API

De JavaScript-editor stuurt een JSON-request naar de field-route.

~~~json
{
  "field": "hero.title",
  "value": "Nieuwe titel"
}
~~~

Bij succes geeft Core dit terug.

~~~json
{
  "field": "hero.title",
  "value": "Nieuwe titel",
  "revision": 2
}
~~~

Bij een onbekend veld, onbekende template of ongeldige waarde geeft Core status 422 terug.

~~~json
{
  "message": "Deyvo page field [hero.onbekend] does not exist."
}
~~~

De hostlayout moet een CSRF-token bevatten en de Core-JavaScript importeren.

~~~blade
<meta name="csrf-token" content="{{ csrf_token() }}">
@deyvoEditor
~~~

~~~js
import '../../vendor/deyvo/core/resources/js/core.js';
~~~

## Publieke Blade-helpers

~~~blade
{{ deyvo_content('home.hero.intro', 'Bestaande introductie') }}
~~~

Deyvo_content verwacht pagina.sectie.veld en geeft een gepubliceerde waarde terug. Tijdens een previewsessie geeft de helper de conceptwaarde terug. De fallback blijft zichtbaar zolang de pagina of waarde nog niet bestaat.

~~~blade
<h1>@deyvoEditable('home.hero.title', 'Bestaande titel')</h1>
~~~

De directive deyvoEditable geeft in bezoekersmodus alleen een geescapeerde tekstwaarde terug. In previewsessie geeft de directive een geescapeerde span terug met deze metadata.

| Attribuut | Waarde |
| --- | --- |
| data-deyvo-field | Volledige pagina-, sectie- en veldsleutel. |
| data-deyvo-type | Schema-veldtype. |
| data-deyvo-url | Beveiligde autosave-route voor de pagina. |
| data-deyvo-options | JSON-selectopties. |

## Previewroute

Core leidt standaard naar /{slug}. Registreer een resolver wanneer de host andere publieke routes gebruikt.

~~~php
use Deyvo\Core\Models\Page;
use Deyvo\Core\Models\PageRevision;
use Deyvo\Core\Pages\PageManager;

app(PageManager::class)->registerPreviewUrlResolver(
    static fn (Page $page, PageRevision $revision): string => route('pages.show', $revision->slug),
);
~~~

De resolver ontvangt de Core-pagina en de te previewen revisie en moet een URL van de hostwebsite teruggeven.

## Grenzen

- Rich text wordt niet ondersteund door dit contract.
- Menu’s zijn nog geen Core-module.
- Media wacht op de zelfstandige Deyvo Media-module.
- Oude tabellen worden niet automatisch geïmporteerd.
- Een legacy-import hoort in een expliciete, herhaalbare importmodule met dry-run, conflictmelding en een brondatamapping per website.
