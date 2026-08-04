# Kirby Plugin dat connecteerd met de SOLIS data

> Show | edit | delete data van SOLIS
> Versie 1.0 

---

<br/>

## ✨ Features
- **Create**: Voeg nieuwe entiteiten en relaties toe aan de Solis-triplestore.
- **Update**: Pas bestaande data aan zonder verlies van modelconsistentie.
- **Delete**: Verwijder data die niet langer nodig is.
- **Solis Integration**: Werkt rechtstreeks op de triplestore waarop het Solis-datamodel is gebaseerd.

<br/>

## Inhoudstabel
- [1. Requirements](#%EF%B8%8F-requirements)
- [2. Instalatie](#instalatie)
- [3. Configuratie](#configuratie)
  - [3.1. Paginastructuur](#paginastructuur)
    - [3.1.1. Mogelijke velden](#mogelijke-velden)
  - [3.2. Views](#views)
  - [3.3. Data afhandeling](#data-afhandeling)

<br/>

## ⚠️ Requirements
- Kirby 5+
- PHP >= 8.3

<br/>

## Instalatie
### Docker
Laat de plugin in je docker image draaien en zet enkel de pagina's die je wilt aanpassen of bijcreëren (zie project structuur) in je project zelf.
```php
ARG KIRBY_PLUGIN_FORMS_BRANCH=main
RUN wget --no-verbose "https://github.com/libis/kirby-plugin-solis/archive/${KIRBY_PLUGIN_SOLIS_BRANCH}.zip" -O /var/www/html/site/plugins/solis.zip \
    && unzip -q /var/www/html/site/plugins/solis.zip -d /var/www/html/site/plugins/ \
    && rm /var/www/html/site/plugins/solis.zip \
    && mv /var/www/html/site/plugins/kirby-plugin-solis-${KIRBY_PLUGIN_SOLIS_BRANCH} /var/www/html/site/plugins/kirby-solis \
    && chown -R www-data:www-data /var/www/html/site/plugins/kirby-solis
```
<br/>

### Manually
Clone de Github repository en kopieer alle files naar je eigen project structuur in `/site/plugins`.

<br/>

## Configuratie
Deze plugin zal niet uitzichzelf werken en heeft enkele data nodig om te werken.

Basisdata (plaats in je `config.php` of voor meer veiligheid in `env.php`):
```php
'libis.solis-records' => [
  'solis-baseUrl' => 'baseurl solis data',
  'solis-apiKey' => '',
],
```

<br/>

## Project structuur
De plugin bevat de generieke functionaliteit om records aan te maken, te bewerken en te verwijderen in SOLIS. Ze weet echter niet welke pagina's beschikbaar moeten zijn, welke entiteiten getoond moeten worden of welke dataverwerking nodig is voordat gegevens naar SOLIS worden verzonden.
Daarom moet je binnen `site/plugins` een extra map `kirby-solis` voorzien waarin je de configuratie van jouw project definieert.

```
site/
└── plugins/
    └── kirby-solis/
        ├── area-views
        ├── data-processing
        ├── pageStructures
        └── views
```

> Bij gebruik binnen een Docker-image hoef je niet de volledige plugin te mounten. Het volstaat om de bovenstaande mappen en bestanden beschikbaar te maken.

<br/>

Bestand/Map | Beschrijving 
|:-----|:----
area-views | Bevat 1 file met alle view files uit de `views` map dat geimmporteerd moeten worden zodat deze in de `index.php` automatisch binnengehaald worden.
pageStructures | Definieert welke pagina's beschikbaar zijn en hoe records worden weergegeven en beheerd.
views | Bevat per record type en per type pagina een file voor het ophalen van de data.
data-processing | Bevat logica om data te transformeren of valideren voordat deze naar SOLIS wordt gestuurd.

<br/>

### area views

<br/>

### PageStructures
Voor elk recordtype waarvoor een beheerpagina beschikbaar moet zijn, maak je een PHP-bestand aan in de map pageStructures.

Niet elk SOLIS-recordtype vereist een eigen pagina. Sommige entiteiten bestaan uitsluitend als onderdeel van een andere entiteit en worden nooit afzonderlijk beheerd. Voor deze recordtypes hoef je geen aparte page structure aan te maken.

Elke page structure bestaat uit een PHP-bestand dat een array retourneert. Deze configuratie wordt gebruikt om automatisch:

- een overzichtspagina met alle records van een bepaald type te genereren;
- een pagina voor het aanmaken van nieuwe records te genereren;
- een detailpagina voor het bekijken en bewerken van bestaande records te genereren.

<br/>

#### Entiteit


#### CodeTabel

#### Mogelijke velden

### Views

### Data-processing
