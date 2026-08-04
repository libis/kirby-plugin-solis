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
area-views | Bevat 1 file met alle view files uit de `views` map dat geimmporteerd moeten worden zodat deze in de `index.php` automatisch binnengehaald worden.
pageStructures | Definieert welke pagina's beschikbaar zijn en hoe records worden weergegeven en beheerd.
views | Bevat per record type en per type pagina een file voor het ophalen van de data.
data-processing | Bevat logica om data te transformeren of valideren voordat deze naar SOLIS wordt gestuurd.

<br/>

### PageStructures
Per record type waar je een pagina wilt voorzien maak je een file aan in de map `pageStructures`. Niet alle record types (entiteiten) moeten een pagina hebben (sommige recordtypes hangen specifiek aan een ander record type vast en zal niet voorkomen in een ander record. In deze gevallen hoef je hier geen apparte pagina dus ook geen pageStructure voor te maken.

Zo een file bevat alle nodige data om een creëer pagina + info (aanpassen) pagina + pagina met een lijst van alle record van dat type te creëren. Deze pagina is een return array in PHP. 
Deze array bevat wat algemene data die voor alle pagina's van toepassing zijn denk maar aan een link een record te bekomen, de plural solis entiteit naam, enkelvoud naam en hoe deze entiteit opgezocht kan worden via de search api.
Buiten de algemene info zal je data kunnen meegeven voor de specifieke data denk maar aan welke tabs staan er bovenaan op deze pagina, welke velden moeten ingevuld zijn voor het creeëren van het record. En welke velden heeft het record als je het in detail bekijkt. Een voorbeeld van zo een pagina vind je in de map pageStructures [exampleStructure](https://github.com/libis/kirby-plugin-solis/blob/main/pageStructures/exampleStructure.php)
Alle codetabellen entiteiten zijn hier wel een uitzondering op deze structuur is net wat anders opgebouwd. Hieronder ga ik verder in op hoe je zo een pagina opbouwt.

#### Entiteit


#### CodeTabel

#### Mogelijke velden

### Views

### Data-processing

### area views
