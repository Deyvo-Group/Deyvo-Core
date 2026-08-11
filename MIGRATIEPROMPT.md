# Migreer een Laravel-site naar Deyvo Core

Kopieer de onderstaande prompt volledig naar een nieuwe Codex-chat die geopend is in de bestaande Laravel-site.

````text
Je werkt in een bestaande Laravel-websiterepository. Migreer deze site zorgvuldig naar `deyvo/core` als gedeelde basislaag. Dit is een verbouwing van de huidige site, geen nieuwe Laravel-applicatie.

## Doel

- De site gebruikt `deyvo/core` als herbruikbare foundation.
- Bestaande functionaliteit, content, routes, modellen en styling blijven werken tenzij je ze bewust en veilig vervangt.
- Gebruik waar passend de Deyvo Core Blade-componenten, basislayout, flashmeldingen, featureflags, healthcheck en dashboard.
- Laat het bestaande authenticatie- en mediasysteem van de site intact.
- Voeg geen authenticatie, users, rollen, permissions, uploads, media-modellen of mediafunctionaliteit aan Deyvo Core toe.
- Gebruik geen Filament. Gebruik voor generieke content en instellingen het ingebouwde Deyvo-dashboard.

## Werkwijze

1. Onderzoek eerst de repository. Lees minimaal `composer.json`, `bootstrap/app.php`, routes, bestaande layouts en Blade-componenten, de Tailwind- en Vite-configuratie, relevante modellen en migraties, en bestaande tests.
2. Geef kort weer welke onderdelen van de huidige site naar Deyvo Core kunnen verhuizen en welke domeinspecifiek moeten blijven.
3. Voer de migratie vervolgens uit. Vraag alleen om verduidelijking wanneer een beslissing risico heeft voor bestaande data, publieke routes of toegangsrechten.
4. Houd wijzigingen beperkt, conventioneel en uitbreidbaar. Behoud de bestaande architectuur waar die goed is. Voeg geen overbodige abstractions, comments of volledige testapplicatie toe.
5. Verwijder, truncateer, rollback of overschrijf geen bestaande data, migraties of authenticatie zonder expliciete toestemming.

## Installatie en configuratie

1. Controleer of de applicatie Laravel 12 en PHP 8.2 of hoger gebruikt. Meld compatibiliteitsproblemen concreet voordat je incompatibele dependencies wijzigt.
2. Installeer de package wanneer die nog niet aanwezig is:

   ```bash
   composer require deyvo/core
   ```

3. Als Composer de package niet kan vinden, inspecteer de fout. Gebruik geen verzonnen repository-URL, token of lokale path-repository. Meld exact wat er ontbreekt.
4. Publiceer de configuratie alleen wanneer `config/deyvo-core.php` nog niet bestaat:

   ```bash
   php artisan vendor:publish --tag=deyvo-config
   ```

5. Stel in de gepubliceerde configuratie alleen waarden in die de hostsite echt nodig heeft. Behoud `name`, healthcheck, locale, timezone, request ID en security headers als veilige defaults, tenzij de huidige site daarmee botst.
6. De serviceprovider wordt door Laravel package discovery geladen. Registreer hem niet dubbel handmatig.

## Tailwind en Vite

1. Behoud de bestaande Vite-entrypoints van de hostsite.
2. Voeg aan de Tailwind CSS 4-entry van de hostsite de package-sources toe. Pas de relatieve paden aan wanneer de entry niet in `resources/css` staat:

   ```css
   @source "../../vendor/deyvo/core/resources/views/**/*.blade.php";
   @source "../../vendor/deyvo/core/resources/js/**/*.js";
   @source "../../vendor/deyvo/core/src/**/*.php";
   ```

3. Importeer de package-JavaScript-entry in de JavaScript-entry van de hostsite als de site Deyvo-alerts of -modals gebruikt:

   ```js
   import '../../vendor/deyvo/core/resources/js/core.js';
   ```

4. Gebruik de bestaande `@vite(...)`-aanroep van de hostlayout. Deyvo Core voegt zelf geen host-assets toe.

## Views en componenten

1. Houd de bestaande hoofd-layout aan als deze scripts, navigatie, SEO, analytics of applicatiespecifieke markup bevat. Neem alleen de Deyvo Core basislayout over wanneer dat geen bestaande functionaliteit verwijdert.
2. Gebruik Deyvo-componenten voor gedeelde interfacepatronen waar dat past:

   ```blade
   <x-deyvo::button>Opslaan</x-deyvo::button>
   <x-deyvo::alert type="success">Opgeslagen</x-deyvo::alert>
   <x-deyvo::badge variant="success">Actief</x-deyvo::badge>
   <x-deyvo::card>Inhoud</x-deyvo::card>
   <x-deyvo::empty-state title="Geen items" />
   <x-deyvo::form.input name="name" label="Naam" />
   ```

3. Gebruik `Deyvo\Core\Support\Flash` voor server-side flashmeldingen en render `<x-deyvo::flash />` in layouts die niet op de Deyvo-layout zijn gebaseerd.
4. Zet alleen werkelijk gedeelde UI in Deyvo Core. Paginaspecifieke of domeinspecifieke componenten blijven in de hostsite.

## Dashboard

1. Activeer het Deyvo-dashboard alleen als de hostsite al een passende authenticatie- of beheermiddleware heeft. Deyvo Core levert zelf geen toegangssysteem.
2. Configureer dan in `.env`:

   ```env
   DEYVO_DASHBOARD_ENABLED=true
   DEYVO_DASHBOARD_PATH=deyvo
   ```

3. Pas `dashboard.middleware` in `config/deyvo-core.php` aan op de bestaande guard of middleware van de hostsite. Gebruik de standaard `['web', 'auth']` alleen wanneer die al bestaat en correct is.
4. Voer uitsluitend bij een geactiveerd dashboard de package-migraties uit:

   ```bash
   php artisan migrate
   ```

5. Gebruik het dashboard voor generieke, sitebrede content en instellingen. Gebruik bijvoorbeeld `Deyvo\Core\Support\SiteContent::body('homepage.intro')` en `Deyvo\Core\Support\SiteSettings::get('contact.email')` in views of services.
6. Vervang geen bestaande domeinmodellen blind door generieke dashboardcontent. Migreer alleen data die werkelijk generiek is, met een nieuwe voorwaartse migratie of expliciete seeder, en behoud de brondata totdat de migratie gevalideerd is.
7. Voeg bestaande, domeinspecifieke beheerpagina's als navigatie-item toe zonder dashboardinternals te wijzigen:

   ```php
   app(\Deyvo\Core\Dashboard\DashboardManager::class)->registerNavigation(
       'Rapportages',
       'reports.index',
       'reports.*',
       40
   );
   ```

8. Maak geen login, gebruikersbeheer, rechtenbeheer, uploads of mediabeheer als onderdeel van deze migratie.

## Routes, middleware en validatie

1. Behoud alle bestaande publieke routes en route-namen, tenzij een veilige, expliciet geteste wijziging nodig is.
2. Controleer dat `/_deyvo/health` niet botst met een bestaande route. Verplaats of schakel de healthcheck uit via configuratie wanneer dat wel zo is.
3. Controleer de impact van de Deyvo locale-, timezone-, request-ID- en security-headers-middleware op bestaande middlewaregroepen. Los conflicten gericht op, zonder hostmiddleware onnodig te herschrijven.
4. Gebruik `@deyvoFeature('naam')` alleen voor echte optionele functionaliteit en registreer de feature in de config van de hostsite.
5. Voer na de implementatie de relevante bestaande tests uit, minimaal `php artisan test` of het aanwezige equivalente testcommando.
6. Voer `composer validate`, `composer install`, `npm install` en `npm run build` uit wanneer deze commando's binnen de repository beschikbaar zijn.
7. Voer `php artisan optimize:clear` uit na configuratie-, route- of viewwijzigingen en controleer de routes met `php artisan route:list`.
8. Los fouten die je tijdens de controles vindt direct op. Meld alleen blockers die je niet veilig zelf kunt oplossen.

## Eindresultaat

- Geef eerst een korte samenvatting van de gemaakte wijzigingen, gegroepeerd per onderdeel.
- Noem alle gewijzigde of toegevoegde bestanden met hun absolute paden.
- Benoem expliciet welke bestaande functionaliteit bewust in de hostsite is gebleven.
- Geef de uitgevoerde validatiecommando's en resultaten weer.
- Benoem eventuele handmatige vervolgstappen, zoals het instellen van dashboardmiddleware, het uitvoeren van migraties of het controleren van gemigreerde content.
- Maak geen commit, push of pull request tenzij ik dat expliciet vraag.
````
