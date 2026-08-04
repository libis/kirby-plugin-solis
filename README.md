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

Elke page structure bestaat uit een PHP-bestand dat een array retourneert. Op basis van deze configuratie genereert de plugin automatisch:

- een overzichtspagina met alle records;
- een pagina voor het aanmaken van nieuwe records;
- een detailpagina voor het bekijken en bewerken van bestaande records.

Een eenvoudige structuur ziet er als volgt uit:
``` php
return [
    'main' => [],
    'add'  => [],
    'edit' => [],
    'list' => [],
];
```

Een volledig voorbeeld vind je in:
[pageStructures/exampleStructure.php](https://github.com/libis/kirby-plugin-solis/blob/main/pageStructures/exampleStructure.php)

<br/>

#### Main
De main sectie bevat algemene configuratie die voor alle schermen gebruikt wordt.

``` php
'main' => [
  'requestsInKirbyLink' => '/records/example/example',
  'type' => 'example',
  'singularType' => 'example',
  'searchType' => 'example',
]
```

Eigenschap | Beschrijving
|:-----|:----
requestsInKirbyLink | Basislink naar een individueel record.
type	| Naam van het recordtype in SOLIS.
singularType | Enkelvoudige naam van het recordtype.
searchType | Type waarmee het record via de SOLIS Search API wordt opgezocht.

<br/>

#### Add
De add sectie bepaalt hoe de pagina voor het toevoegen van nieuwe records wordt opgebouwd.

``` php
'add' => [
  'tabs' => [...],
  'amountOfColumns' => 2,
  'fields' => [...]
]
```

Eigenschap | Beschrijving
|:-----|:----
tabs | Tabs die bovenaan het scherm worden weergegeven.
amountOfColumns | Aantal kolommen waarin het formulier wordt opgebouwd (1 of 2).
fields | Velden die ingevuld moeten worden bij het aanmaken van een record. Zie mogelijke velden voor de mogelijkheden.

<br/>

#### Edit
De edit sectie bepaalt hoe een bestaand record wordt weergegeven en aangepast.

``` php
'edit' => [
  'titleSelector' => 'tag.tag_name',
  'tabs' => [...],
  'amountOfColumns' => 2,
  'fields' => [...]
]
```

Eigenschap | Beschrijving
|:-----|:----
titleSelector | Pad naar de waarde die als titel van het record wordt weergegeven.
tabs | Tabs die bovenaan het scherm worden weergegeven.
amountOfColumns | Aantal kolommen waarin het formulier wordt opgebouwd (1 of 2).
fields | Velden die bekeken en aangepast kunnen worden. Zie mogelijke velden voor de mogelijkheden.

<br/>

#### List
De list sectie definieert het overzichtsscherm van een recordtype.

``` php
'list' => [
  'titleSelector' => 'tag.tag_name',
  'infoSelector' => 'role.value',
  'tabs' => [...]
]
```

Eigenschap | Beschrijving
|:-----|:----
titleSelector | Waarde die als titel van een record wordt weergegeven in de lijst.
infoSelector | Extra informatie die onder de titel wordt getoond.
tabs | Tabs die bovenaan het scherm worden weergegeven.

<br/>

#### Codetabellen

Codetabel-entiteiten maken gebruik van een afwijkende configuratiestructuur. Deze structuur wordt toegepast voor entiteiten die binnen SOLIS fungeren als vaste waardelijsten.

Alle codetabellen worden gegroepeerd op één overzichtspagina. Omdat deze entiteiten uitsluitend bestaan uit een lijst van waarden, worden er geen afzonderlijke pagina's voorzien voor het aanmaken, weergeven of oplijsten van records. De volledige configuratie wordt bijgehouden in één centrale configuratiefile.

Onderstaande configuratiegegevens zijn vereist:

```php
return [
    'main' => [
        'requestsInKirbyLink' => '/records/codetables', 
        'name' => 'Codetables'
    ],
    'codeTables' => [
        [
            'name' => 'example', 
            'solisSearchRecord' => 'example', 
            'label' => 'Example', 
            'style' => '--width:1/3', 
        ]
    ]
];
```

Eigenschap | Beschrijving
|:-----|:----
requestsInKirbyLink | URL waarnaar wordt teruggekeerd na acties zoals het aanmaken of wijzigen van een record.
name (main) | Naam van deze pagina
name (codeTables) | Naam van de kolom voor de codeTabel
solisSearchRecord | Identifier die gebruikt wordt om records van dit type op te zoeken in SOLIS.
label | Weergavenaam van de kolom in de gebruikersinterface.
style  | Definieert de breedte van de kolom binnen het overzichtsscherm.

<br/>

#### Mogelijke velden
Je kan alle standaard Kirby-veldtypes gebruiken om data te tonen, aangevuld met enkele speciaal ontwikkelde velden.

Elk veld maakt gebruik van de volgende basisstructuur:

```php
[
    'name' => '',    
    'align' => '',  
    'type' => '', 
    'icon' => '',
    'label' => 'Tag', 
    'solis-selector' => 'tag', 
    'style' => '--width: 1/1;',
    'min' => 1
],
```

Eigenschap | Beschrijving
|:-----|:----
Name | Unieke naam van het veld binnen de pagina. Gebruik bij voorkeur dezelfde naam als de naam die Solis verwacht bij het opslaan van de pagina.
Align | Mogelijke waarden: left of right. Enkel van toepassing wanneer het formulier uit twee kolommen bestaat.
Type | Het type van het veld. Zie de beschikbare veldtypes hieronder.
Icon | Optioneel icoon dat aan het label wordt toegevoegd, bijvoorbeeld om aan te geven dat een veld vertaalbaar is.
Label | Label dat wordt weergegeven bij het veld.
Solis-selector | Geeft aan hoe de waarde wordt opgehaald uit de ontvangen Solis JSON-data.
Style | Extra styling voor het veld. Moet minstens een breedte bevatten, bijvoorbeeld --width: 1/1;.
Min | Enkel toevoegen indien het veld verplicht is. Gebruik 1 voor verplichte velden of een hogere waarde bij velden waarvoor meerdere waarden vereist zijn.

<br/>

##### Alle velden
- **Text-field** <br/>
  Dit is een standaard Kirby-veld. Alle beschikbare opties vind je in de officiële documentatie: [handleiding](https://lab.getkirby.com/public/lab/components/fields/text). Extra configuratie kan worden meegegeven via `componentsOptions`.
   ```php
     'componentsOptions' =>[
          'after' => '€',
      ],
   ```

<br/>
   
- **Textarea-field** <br/>
  Dit is een standaard Kirby-veld. Alle beschikbare opties vind je in de officiële documentatie: [handleiding](https://lab.getkirby.com/public/lab/components/fields/textarea). Standaard bevat dit veld formatteringsknoppen (zoals bold). Deze kunnen uitgeschakeld worden via `componentsOptions` array.
  ```php
     'componentsOptions' =>[
          'buttons' => false,
          'size' => 'medium' 
      ],
   ```

<br/>
  
- **Number-field** <br/>
  Dit is een standaard Kirby-veld. Alle beschikbare opties vind je in de officiële documentatie: [handleiding](https://lab.getkirby.com/public/lab/components/fields/number). Extra configuratie wordt meegegeven via componentsOptions.
  ```php
     'componentsOptions' =>[
          'min' => 1
      ],
   ```
  Hier kunnen onder andere minimumwaarden, maximumwaarden en stappen (step) worden ingesteld.

  > Opmerking: De min binnen componentsOptions bepaalt de minimale numerieke waarde en niet of het veld verplicht is. Om een veld verplicht te maken gebruik je:
  
  ```php
     'required' => true,
  ```

<br/>

- Select-field
  
- **Toggle-field** <br/>
    Dit is een standaard Kirby-veld. Alle beschikbare opties vind je in de officiële documentatie: [handleiding](https://lab.getkirby.com/public/lab/components/fields/toggle). Extra configuratie kan worden meegegeven via `componentsOptions`.
   ```php
     'componentsOptions' =>[
          'checked' => true,
      ],
   ```

<br/>

- Toggles-field
- Entity
  - Relation-field
  - Add-multiple-values-field

Andere basisvelden van Kirby zullen mogelijks ook werken maar deze zijn nog niet getest. Bekijk de andere Kirby velden [hier](https://lab.getkirby.com/public/lab/components/fields/checkboxes)
### Views

### Data-processing
