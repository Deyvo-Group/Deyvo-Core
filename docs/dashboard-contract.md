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
    'vite' => [
        'resources/css/app.css',
        'resources/js/app.js',
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
| vite | Array met bestaande Vite-entrypoints van de host | Laadt Tailwind en de Core-JavaScript in het zelfstandige dashboard. Standaard gebruikt Core `resources/css/app.css` en `resources/js/app.js`. |
| pages.enabled | Boolean | Activeert pagina’s, revisies, preview en editorroutes. |

Core levert geen login, users, rollen, permissions, uploads of mediafunctionaliteit. De website levert de middleware die toegang tot het dashboard geeft.

## Dashboard JSON

Het JSON-bestand heeft vier onafhankelijke onderdelen.

~~~json
{
  "pages": [],
  "layouts": [],
  "blocks": [],
  "templates": []
}
~~~

pages definieert gegroepeerde algemene instellingen en content. layouts definieert globale header-, footer- of andere layoutonderdelen. blocks definieert de bouwstenen van de pagina-editor. templates definieert pagina’s met versieerbare secties of een blokbuilder.

Core gebruikt standaard de Laravel Vite-entrypoints. Alleen websites met andere entrypoints configureren dashboard.vite expliciet.

~~~css
@source "../../vendor/deyvo/core/resources/views/**/*.blade.php";
@source "../../vendor/deyvo/core/src/**/*.php";
~~~

~~~js
import '../../vendor/deyvo/core/resources/js/core.js';
~~~

Importeer dit bestand na JavaScript-imports van de host. Core koppelt zijn stylesheet aan deze entry en geeft dashboard- en editorcontrols binnen hun Deyvo-scope voorrang, zonder algemene websitestijlen buiten die scope te wijzigen.

### Core interface-styling

De aanvullende Core-styling staat standaard aan. Een website kan deze uitzetten zonder dashboard-, editor- of packagefunctionaliteit uit te schakelen.

~~~php
'ui' => [
    'styles' => [
        'enabled' => false,
    ],
],
~~~

Of gebruik `DEYVO_CORE_STYLES_ENABLED=false` in het `.env`-bestand van de website.

### Dashboardgradient

Core geeft het dashboard standaard een blauwe gradient. Een website kan een eigen CSS-gradient meegeven. Wanneer deze ontbreekt, blijft de standaardgradient actief.

~~~php
'ui' => [
    'dashboard' => [
        'gradient' => 'linear-gradient(135deg, #eff6ff 0%, #dbeafe 48%, #cffafe 100%)',
    ],
],
~~~

Gebruik `DEYVO_DASHBOARD_GRADIENT` voor dezelfde instelling in `.env`.

### Activiteit en auteurs

Core gebruikt uitsluitend de ingelogde gebruiker van de hostmiddleware. Core voegt geen users, login of permissies toe. De dashboardkop en editorbalk tonen de huidige gebruiker wanneer de host die levert.

Nieuwe en bijgewerkte paginarevisies bewaren een snapshot van de maker en laatste bewerker. Core registreert daarnaast content-, instellingen-, pagina-, preview- en foutacties in `deyvo_audit_logs`. Iedere regel bevat de gebruiker, request-id, requestpad, IP-adres en context zonder instellingenwaarden op te slaan.

De activiteitspagina staat standaard in de dashboardnavigatie. Schakel registratie alleen uit wanneer nodig.

~~~php
'audit' => [
    'enabled' => false,
],
~~~

Of gebruik `DEYVO_AUDIT_ENABLED=false` in `.env`. Voer na een Core-update altijd `php artisan migrate` uit voor de nieuwe audit- en auteurkolommen.

### Account en uitloggen

De dashboardkop en editorbalk tonen de naam van de ingelogde gebruiker uit de hostapplicatie. Core toont de uitlogknop uitsluitend als de geconfigureerde named route bestaat. Fortify gebruikt standaard `logout`.

~~~php
'dashboard' => [
    'logout_route' => 'logout',
],
~~~

Gebruik `DEYVO_DASHBOARD_LOGOUT_ROUTE` wanneer de website een andere routenaam gebruikt. Core voegt zelf geen login- of logoutroute toe.

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
| type | text, textarea, html, email, url, select of boolean | text | Invoer en validatie. |
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

## Layoutbeheer

layouts voegt een eigen **Layout**-onderdeel toe aan de dashboardnavigatie. Iedere definitie krijgt een aparte bewerkpagina. Gebruik bijvoorbeeld header en footer voor globale inhoud die op meerdere publieke pagina's verschijnt.

~~~json
{
  "layouts": [
    {
      "key": "header",
      "label": "Header",
      "description": "Beheer navigatie en de primaire actie.",
      "sort": 10,
      "fields": [
        {
          "key": "layout.header.primary_cta",
          "label": "Primaire knop",
          "type": "text",
          "storage": "content",
          "content_title": "Header primaire knop"
        }
      ]
    },
    {
      "key": "footer",
      "label": "Footer",
      "description": "Beheer de globale footerinhoud.",
      "sort": 20,
      "fields": [
        {
          "key": "layout.footer.brand_intro",
          "label": "Introductie",
          "type": "textarea",
          "storage": "content",
          "content_title": "Footer introductie"
        }
      ]
    }
  ]
}
~~~

De velden hebben exact dezelfde validatie en opslagregels als algemene instellingen. Core maakt geen header- of footer-HTML: de website rendert de waarden zelf met `deyvo_content` of `SiteSettings`.

~~~blade
{{ deyvo_content('layout.header.primary_cta', 'Neem contact op') }}
{{ deyvo_content('layout.footer.brand_intro', 'Een heldere introductie.') }}
~~~

Iedere opslagactie krijgt de activiteit `layout.updated`, met de layoutkey en gewijzigde veldsleutels. Waarden zelf komen niet in het activiteitenlog.

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

Templatevelden ondersteunen text, textarea, html, email, url, select en boolean. Dezelfde validatieregels voor required, help, placeholder en options gelden als bij algemene instellingen.

## Blokbuilder

Een buildertemplate heeft geen verplichte secties. De template geeft expliciet op welke blokken een beheerder mag toevoegen. De volgorde van blocks in een revisie is ook de volgorde waarin de website ze rendert.

~~~json
{
  "blocks": [
    {
      "key": "hero",
      "label": "Hero",
      "description": "Opening van een pagina.",
      "category": "Introductie",
      "fields": [
        {
          "key": "heading",
          "label": "Titel",
          "type": "text",
          "required": true
        },
        {
          "key": "body",
          "label": "Introductie",
          "type": "textarea"
        }
      ]
    },
    {
      "key": "text",
      "label": "Tekst",
      "category": "Inhoud",
      "fields": [
        {
          "key": "body",
          "label": "Tekst",
          "type": "textarea",
          "required": true
        }
      ]
    }
  ],
  "templates": [
    {
      "key": "builder-page",
      "label": "Builderpagina",
      "builder": {
        "enabled": true,
        "blocks": ["hero", "text"]
      },
      "sections": []
    }
  ]
}
~~~

| Blokveld | Verwachting | Resultaat |
| --- | --- | --- |
| key | Kleine letters, cijfers en koppeltekens | Stabiel bloktype in een revisie. |
| label | Niet-lege tekst | Naam in de blokkiezer en inspector. |
| description | Tekst | Uitleg in de blokkiezer en inspector. |
| category | Tekst | Groepeert blokken in de blokkiezer. Standaard Algemeen. |
| fields | Array, ook leeg toegestaan | Velden van dit blok. |
| builder.enabled | Boolean | Activeert de builder voor deze template. Standaard true zodra builder bestaat. |
| builder.blocks | Niet-lege array met bekende blokkeys | De enige blokken die op de template mogen staan. |
| sections | Array | Mag leeg zijn bij een actieve builder. |

Blokvelden gebruiken dezelfde types en validatie als templatevelden: text, textarea, html, email, url, select en boolean. Een beheerder kan blokken toevoegen, selecteren, verplaatsen, dupliceren en verwijderen. De inspector schrijft alle veldwaarden naar een verborgen JSON-invoer; het gewone paginaformulier bewaart vervolgens een nieuw concept.

### HTML-codeblok

Het type html opent in de builder een CodeMirror 6-editor met HTML-syntaxhighlighting, tagsuggesties, automatische sluiting van tags, regelnummers en Tab-inspringing. De CodeMirror-pakketten zijn MIT-gelicentieerd en worden alleen geladen wanneer een paginaformulier een HTML-veld bevat.

~~~json
{
  "key": "html",
  "label": "HTML",
  "description": "Een veilige HTML-sectie met code-editor.",
  "category": "Aangepast",
  "fields": [
    {
      "key": "html",
      "label": "HTML",
      "type": "html",
      "required": true,
      "help": "Gebruik alleen structurele HTML."
    }
  ]
}
~~~

Neem dit blok op in `builder.blocks` van een template. Beheerders kunnen het dan via **Blok toevoegen** plaatsen, dupliceren, verwijderen en sorteren zoals ieder ander blok.

Core ontsmet HTML bij het opslaan en vlak voor het renderen. Structurele HTML en veilige links blijven staan. Scripts, styles, event-handlers, forms, embeds, SVG en onveilige URL's worden verwijderd. Gebruik dit blok niet voor JavaScript, CSS, trackingcodes, authenticatie of media-embeds.

Core publiceert een bruikbaar startschema met html, hero, text, quote, call-to-action en divider. Dit startschema bevat een standaardpage-template met de builder ingeschakeld. De website mag dit schema uitbreiden of vervangen.

### Revisieblokken

Core slaat de builder op in deyvo_page_revisions.blocks als een geordende JSON-array.

~~~json
[
  {
    "id": "block-hero-01",
    "type": "hero",
    "data": {
      "heading": "Bouw met blokken",
      "body": "Maak elke pagina op maat."
    }
  },
  {
    "id": "block-text-01",
    "type": "text",
    "data": {
      "body": "Deyvo bewaart iedere wijziging als een conceptrevisie."
    }
  }
]
~~~

| Eigenschap | Verwachting | Core-gedrag |
| --- | --- | --- |
| id | Unieke kleine letters, cijfers en koppeltekens | Blijft stabiel voor selectie en eventuele host-JavaScript. |
| type | Blokkey die door de template is toegestaan | Bepaalt de valide velden en publieke view. |
| data | Object met uitsluitend bekende blokvelden | Core valideert en normaliseert de waarden op opslaan. |
| arrayvolgorde | Geordende lijst | Bepaalt de publieke render-volgorde. |

Onbekende bloktypen, dubbele IDs, onbekende velden en ongeldige waarden worden niet opgeslagen. Bestaande secties en blokken kunnen op dezelfde template naast elkaar bestaan.

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
| title, slug, template, sections, blocks, seo | Nieuwe of bijgewerkte deyvo_page_revisions-revisie. |
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
| POST | /dashboard/pages/{page}/preview/stop | deyvo.dashboard.pages.preview.stop | Stopt previewsessie en leidt naar de live pagina. |
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

Tijdens een actieve preview rendert deyvoEditor een vaste editoroverlay. Deze toont dat de editor actief is, de paginatitel, het pad, de conceptversie en de actuele opslagstatus. De overlay bevat ook een Dashboard-link en een actie om de editor te verlaten. Bezoekers zien deze overlay nooit.

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

### Blokken renderen

Gebruik de component in een publieke Blade-view. Bezoekers zien de gepubliceerde revisie. Tijdens een actieve Core-preview ziet dezelfde component het concept.

~~~blade
<x-deyvo::blocks page="over-deyvo" />
~~~

Of vraag de ruwe, geordende blokdata op voor een eigen loop.

~~~blade
@foreach (deyvo_blocks('over-deyvo') as $block)
    <section data-block="{{ $block['id'] }}">
        {{ data_get($block, 'data.heading') }}
    </section>
@endforeach
~~~

Core levert standaardviews voor hero, text, quote, call-to-action en divider. Een host overschrijft de weergave van een blok door resources/views/deyvo-blocks/{bloktype}.blade.php te maken. De view ontvangt altijd een array in $block met id, type en data. Daarmee blijven design en domeinspecifieke markup van de website, terwijl Core de blokdata, concepten en publicatie beheert.

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

- Rich text en geneste blokken worden niet ondersteund door dit contract.
- Menu’s zijn nog geen Core-module.
- Media wacht op de zelfstandige Deyvo Media-module.
- Oude tabellen worden niet automatisch geïmporteerd.
- Een legacy-import hoort in een expliciete, herhaalbare importmodule met dry-run, conflictmelding en een brondatamapping per website.
