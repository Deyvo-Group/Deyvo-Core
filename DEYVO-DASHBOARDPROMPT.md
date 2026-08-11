# Prompt Voor De Deyvo-Website

Gebruik deze prompt in een nieuwe Codex-chat die geopend is in /Users/dirk/Documents/GitHub/DEYVO.

Je werkt in de bestaande Laravel-website op /Users/dirk/Documents/GitHub/DEYVO. Configureer het nieuwe Deyvo Core JSON-dashboard voor deze website. De dashboardstructuur moet door de website zelf worden geleverd via een lokaal JSON-bestand, zodat per klant alleen de gewenste beheeropties zichtbaar zijn.

## Context

- De website gebruikt Deyvo Core en Laravel 12.
- Deyvo Core bevat een JSON-dashboardfunctie met DashboardSchema, de publish-tag deyvo-dashboard-schema en routes onder het bestaande dashboardpad.
- De website heeft bestaande, niet-gecommitte wijzigingen. Behoud die altijd. Herstel geen verwijderde bestanden, voer geen reset uit en wijzig geen niet-gerelateerde code.
- De website heeft al App\Support\CoreSettings. Gebruik deze helper voor settingwaarden met een veilige fallback.
- De website heeft eigen authenticatie via Fortify. Behoud die. Deyvo Core levert en krijgt geen authenticatie, users, permissions, uploads of mediafunctionaliteit.

## Doel

1. Controleer eerst de huidige Git-status, composerversie van deyvo/core, config/deyvo-core.php, resources/views/vendor/deyvo/dashboard/layout.blade.php, de publieke views, controllers, routes en bestaande tests.
2. Controleer dat de geïnstalleerde release van deyvo/core de JSON-dashboardfunctie bevat. De package moet DashboardSchema en de tag deyvo-dashboard-schema bevatten.
3. Ontbreekt die release nog op Packagist, stop dan en meld dat duidelijk. Gebruik geen lokale path-repository en wijzig Deyvo Core zelf niet vanuit deze repository.
4. Maak daarna een klein, concreet JSON-manifest dat past bij de huidige website. Gebruik alleen velden die ook echt in de publieke website worden gebruikt.
5. Koppel de publieke Blade-views aan de dashboardwaarden, maar behoud alle huidige zichtbare teksten en links als fallback totdat een beheerder een waarde opslaat.

## Package en dashboard instellen

1. Werk deyvo/core alleen bij als de gepubliceerde versie de JSON-dashboardfunctie bevat.
2. Publiceer het JSON-startbestand alleen wanneer resources/deyvo/dashboard.json nog niet bestaat:

    php artisan vendor:publish --tag=deyvo-dashboard-schema

3. Voeg in config/deyvo-core.php binnen dashboard deze configuratie toe zonder de bestaande middleware, het dashboardpad of de standaardnavigatie te verwijderen:

    'schema' => [
        'path' => 'resources/deyvo/dashboard.json',
    ],

4. Behoud de bestaande dashboardmiddleware. Controleer dat alleen ingelogde gebruikers toegang houden.
5. Controleer de bestaande gepubliceerde dashboardlayout. Deze moet navigatieparameters ondersteunen, anders werken JSON-pagina’s niet. Gebruik voor dashboardlinks:

    route($item['route'], $item['parameters'] ?? [])

6. Markeer een JSON-pagina alleen actief wanneer zowel de route als de page-routeparameter overeenkomen. Neem hiervoor de actuele layout uit de nieuwe Core-release als uitgangspunt en behoud alleen echte website-specifieke layoutaanpassingen.

## JSON-manifest

1. Maak of vul resources/deyvo/dashboard.json met valide JSON. Het topniveau bevat een pages-array.
2. Gebruik alleen de veldtypen text, textarea, email, url, select en boolean.
3. Gebruik storage content uitsluitend voor publiseerbare tekst die via Deyvo\Core\Support\SiteContent::body gelezen wordt. Gebruik voor alle overige waarden de standaard storage setting.
4. Gebruik betekenisvolle keys met kleine letters, punten, koppeltekens of underscores.
5. Gebruik geen media-, upload-, user-, role- of permissionvelden.
6. Start op basis van wat daadwerkelijk in de website voorkomt met pagina’s voor bijvoorbeeld algemene contactgegevens, homepage-inhoud, SEO en sociale links. Voeg geen verzonnen beheeropties toe.

Gebruik dit als vormvoorbeeld en vervang uitsluitend waarden en velden na analyse van de bestaande website:

    {
      "pages": [
        {
          "key": "algemeen",
          "label": "Algemeen",
          "description": "Beheer algemene contactgegevens.",
          "sort": 40,
          "fields": [
            {
              "key": "contact.email",
              "label": "E-mailadres",
              "type": "email",
              "required": true
            },
            {
              "key": "contact.phone",
              "label": "Telefoonnummer",
              "type": "text"
            },
            {
              "key": "social.linkedin_url",
              "label": "LinkedIn URL",
              "type": "url"
            }
          ]
        },
        {
          "key": "homepage",
          "label": "Homepage",
          "description": "Beheer zichtbare inhoud op de homepage.",
          "sort": 50,
          "fields": [
            {
              "key": "homepage.hero.title",
              "label": "Hero-titel",
              "type": "text",
              "required": true
            },
            {
              "key": "homepage.hero.intro",
              "label": "Hero-introductie",
              "type": "textarea",
              "storage": "content",
              "content_title": "Homepage hero introductie"
            }
          ]
        },
        {
          "key": "seo",
          "label": "SEO",
          "description": "Beheer de basisinstellingen voor zoekmachines.",
          "sort": 60,
          "fields": [
            {
              "key": "seo.default_title",
              "label": "Standaard paginatitel",
              "type": "text"
            },
            {
              "key": "seo.default_description",
              "label": "Standaard metabeschrijving",
              "type": "textarea"
            },
            {
              "key": "seo.indexable",
              "label": "Opneembaar in zoekmachines",
              "type": "boolean"
            }
          ]
        }
      ]
    }

## Publieke website koppelen

1. Vervang alleen de hardgecodeerde tekst, contactgegevens en links die in het JSON-manifest voorkomen.
2. Gebruik voor settings App\Support\CoreSettings::get met de bestaande zichtbare waarde als tweede argument.
3. Gebruik voor content Deyvo\Core\Support\SiteContent::body met de bestaande zichtbare tekst als tweede argument.
4. Escape alle waarden via normale Blade-uitvoer. Render nooit ongefilterde HTML uit dashboardwaarden.
5. Laat domeinspecifieke pagina-inhoud, contactformulierlogica, e-mailverzending, login, Fortify, Livewire en caching ongemoeid tenzij de koppeling dit strikt nodig maakt.
6. Maak geen data-kopie of nieuwe migratie wanneer de bestaande Deyvo-tabellen al beschikbaar zijn. Draai uitsluitend php artisan migrate wanneer de package-migraties nog niet zijn uitgevoerd en controleer eerst welke migraties gepland zijn.

## Validatie

1. Controleer dat php artisan route:list de bestaande dashboardroutes en de custom-routes toont.
2. Controleer dat een niet-ingelogde bezoeker geen toegang krijgt tot het dashboard.
3. Controleer ingelogd dat elke JSON-pagina in de navigatie zichtbaar is, opent en waarden opslaat.
4. Controleer dat aangepaste contact-, homepage- en SEO-waarden in de publieke website verschijnen en dat de huidige hardgecodeerde waarden als fallback werken wanneer er nog geen dashboardwaarde bestaat.
5. Voer de relevante tests uit, minimaal composer test of php artisan test, gevolgd door composer validate, npm run build en php artisan optimize:clear.
6. Los gevonden fouten direct op.

## Eindresultaat

- Geef een korte samenvatting van het JSON-schema en welke publieke onderdelen eraan gekoppeld zijn.
- Noem alle gewijzigde bestanden met absolute paden.
- Benoem expliciet welke bestaande functionaliteit bewust niet is aangepast.
- Rapporteer alle validatiecommando’s en resultaten.
- Maak geen commit, push of pull request tenzij ik dat expliciet vraag.
