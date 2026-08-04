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

  > Opmerking: De min / max buiten componentsOptions bepaalt de minimale / maximale numerieke waarde en niet of het veld verplicht is. Om een veld verplicht te maken gebruik je:
  
  ```php
     'required' => true,
  ```

<br/>

- **Select-field**:
  Dit is een standaard Kirby-veld. Om dit veld correct te laten werken, zijn enkele extra configuratieopties vereist.
  
  ComponentsOptions:
  ``` php
  'componentsOptions' => [
      'type' => 'select',
      'entity' => true,
      'apiEndpoint' => '/_logic/codetable?name=roles&language={language}&from_cache=0',
      'textValue' => 'value',
      'valueValue' => 'id',
      'valueSelector' => 'id',
      'inputType' => 'select',
      'sendType' => 'object',
      'options' => []
  ],
  ```
  Eigenschap | Beschrijving
  |:-----|:----
  type | Dit moet altijd select zijn.
  entity | Geeft aan of de opties afkomstig zijn van een externe entity of recordtype.
  apiEndpoint | Endpoint waarmee de beschikbare opties worden opgehaald. Enkel vereist wanneer entity op true staat.
  textValue | Naam van het veld in de ontvangen data dat als zichtbare tekst in de dropdown wordt weergegeven. Enkel vereist wanneer entity op true staat.
  valueValue | Naam van het veld in de ontvangen data dat als waarde van de optie wordt gebruikt. Enkel vereist wanneer entity op true staat.
  valueSelector | Enkel nodig wanneer sendType gelijk is aan strings en de waarde uit een object opgehaald moet worden. In de meeste gevallen niet nodig en enkel van toepassing als de entity op true staat. 
  inputType | Moet altijd select zijn. Wordt gebruikt wanneer het veld deel uitmaakt van een complex veld en entity op true staat.
  sendType | Bepaalt hoe de waarde naar Solis wordt verstuurd. Mogelijke waarden zijn object of strings. Enkel van toepassing als het veld deel uitmaakt van een complex veld en de entity op true staat.
  options | Vast ingestelde opties. Enkel gebruiken wanneer entity op false staat. Verwacht een array van objecten met een label en value.

  DataFields:
  Voorzie deze configuratie altijd, ook op toevoegschermen waar ze niet rechtstreeks gebruikt wordt. Wanneer reeds opgeslagen data geladen wordt, bepaalt deze configuratie welke velden gebruikt worden om de geselecteerde waarde opnieuw op te bouwen.
  ``` php
  'dataFields' => [
        'id' => 'id',
        'text' => 'value',
        'value' => 'id',
    ],
  ```

  Meer informatie:
  Meer informatie over een select veld vind je in de officiële documentatie: [handleiding](https://lab.getkirby.com/public/lab/components/fields/select).

  <br/>
  
- **Toggle-field** <br/>
    Dit is een standaard Kirby-veld. Alle beschikbare opties vind je in de officiële documentatie: [handleiding](https://lab.getkirby.com/public/lab/components/fields/toggle). Extra configuratie kan worden meegegeven via `componentsOptions`.
   ```php
     'componentsOptions' =>[
          'checked' => true,
      ],
   ```

<br/>

- **Toggles-field** <br/>
  Dit is een standaard Kirby-veld. Om dit veld correct te laten werken, zijn enkele extra configuratieopties vereist.
  ``` php
  'componentsOptions' => [
      'grow' => true,
      'options' => [
          [
              'value' => '',
              'text' => '',
              'icon' => '',
          ],
      ],
      'labels' => true,
  ],
  ```
  Eigenschap | Beschrijving
  |:-----|:----
  grow | De toggle zal de options over de volledige breedte tonen
  options | Geef een array met options mee die telkens een value, label en eventueel een icoon bevat
  labels | Er wordt in de toggle ook tekstueel meegegeven wat de optie is (label)
  
  Meer informatie over een toggles veld vind je in de officiële documentatie: [handleiding](https://lab.getkirby.com/public/lab/components/fields/toggle).

  <br/>
  
- **Entity** <br/>
  Dit is een speciaal ontwikkeld veldtype om Solis data te behandelen. Dit veld maakt het mogelijk om entitities te creëeren of selecteren afhankelijk van het gekozen subType (relation field of add multiple values field)
  
  - Relation-field <br/>
    Dit veld maakt het mogelijk om meerdere id's van andere entiteittypes als value op te slaan. De gebruiker zal door de entiteiten kunnen zoeken. Je kan een max en min meegeven.
    Voeg volgende velden extra toe:
    ``` php
    'subType' => 'relation-field',
    'componentsOptions' => [
        'imageValue' => 'content_url',
        'textValue' => 'image_object_name|filename',
        'infoValue' => 'id',
        'recordType' => 'imageObject',
        'min' => 1
    ],
    'dataFields' => [
        'id' => 'id',
        'text' => ['image_object_name', 'id'],
        'info' => 'id',
        'image' => [
            'src' => 'content_url'
        ]
    ],
    ```
    Eigenschap | Beschrijving
    |:-----|:----
    imageValue (componentsOptions) | Indien er een imageurl aanwezig is in de opgevraagde data
    textValue (componentsOptions) | veld voor het tonen van het label | staat voor of en . staat voor dieper in een structuur graven
    infoValue (componentsOptions) | veld voor het opslagen van de value | staat voor of en . staat voor dieper in een structuur graven. (vaak is dit de id)
    recordtype (componentsOptions) | het record type waar uitgekozen mag worden (geef naam van hoe het in de search api kan bevraagd wordenà
    min / max (componentsOptions) | Moet er een entiteit gekozen worden en hoeveel.
    id (dataFields) | als er al values zijn opgeslagen hoe komen we aan de value (niet van toepassing bij add screen)
    text (dataFields) | als er al values zijn opgeslagen hoe komen we aan het label (niet van toepassing bij add screen)
    info (dataFields) | als er al values zijn opgeslagen hoe komen we aan de value (niet van toepassing bij add screen)
    image (dataFields) | als er al values zijn opgeslagen hoe komen we aan de image (niet van toepassing bij add screen of als er geen afbeelding getoond is) - dit moet een array zijn zoals aangegeven is hierboven.

    <br/>

  - Add-multiple-values-field <br/>
    Zoals bovenaan beschreven moet niet voor elk recordtype aparte schermen voor het beheren van dit recordtype zijn. Soms moet je dit gewoon bij een ander recordtype aanmaken en niet meer hergebruiken. Denk maar aan identifiers, ...

    Voeg volgende dingen toe om dit te laten werken:
    ``` php
      'componentsOptions' => [
          'url' => '/records/comments/comment',
          'singularTypeName' => 'Comment',
          'fields' => [
              'value' => [
                  'label' => 'Value',
                  'type' => 'text',
                  'required' => true,
              ]
          ],
          'save' => true,
      ],
      'dataFields' => [
          'value' => 'value'
      ],
    ```
    Eigenschap | Beschrijving
    |:-----|:----
    url (componentsOptions) | de request die uitgevoerd moet worden voor toevoegen of bewerken
    singularTypeName (componentsOptions) | enkelvoudige naam van het type
    fields (componentsOptions) | Array van de velden die dit type bevat
    dataFields | Reeds bestaande records van dit type hoe wordt de data voor dat subveld bekomen
    
    <br/>
    
- **multiple_records_of_type** <br/>
  Dit is een speciaal ontwikkeld veldtype om Solis data te behandelen. DIt veld maakt het mogelijk om meerdere velden van hetzelfde type toe tevoegen aan één value. Bijvoorbeeld je moet meerdere url's kunnen toevoegen aan het veld links.
  Je kan dit veld laten terugkomen in een `add-multiple-values-field` of gewoon als veld toevoegen.
  
  Als veld voeg je volgende velden nog toe via `componentsOptions`:
  ``` php
    'componentsOptions' => [
      'inputType' => 'string,
      'valueSelector' => null,
      'sendType' => 'strings',
  ],
  ```
  Eigenschap | Beschrijving
  |:-----|:----

  In een `add-multiple-values-field` veld:
  ``` php
  'fieldvalue' => [
      'label' => 'Url',
      'type' => 'multiple_records_of_type',
      'inputType' => 'string',
      'valueSelector' => null,
      'sendType' => 'strings',
  ]
  ```
  Eigenschap | Beschrijving
  |:-----|:----
  fieldValue | id van het veld waar het in opgeslagen wordt
  label | het label van het veld
  type | Dit is altijd multiple_records_of_type
  inputType | Veld type dat je meerdere malen kan invullen. (simpele velden zoals text, textarea, number, ...)
  valueSelector | Bij iets complexere velden om de juiste velden weg te schrijven. Dit is meestal null
  sendType | export value type van het veld (array, string, ...)
  
<br/>

Andere basisvelden van Kirby zullen mogelijks ook werken maar deze zijn nog niet getest. Bekijk de andere Kirby velden [hier](https://lab.getkirby.com/public/lab/components/fields/checkboxes)

<br/>

### Views
Maak per recordtype dat een eigen pagina's krijgt een map aan. In elke map zitten 3 files (1 voor elk pagina type - create, details, list). In deze file ga je ervoor zorgen dat de correcte data naar de correcte view gestuurd zal worden. De nodige data uit solis zal via api opgehaald worden in deze files. Een [voorbeeld](https://github.com/libis/kirby-plugin-solis/tree/main/views/example)

<br/>

#### Create 
De file dient voor de gegevens van het toevoegen van een record van het type door te geven naar het scherm. [Voorbeeld](https://github.com/libis/kirby-plugin-solis/blob/main/views/example/addExampleRecord.php)

Werking
Wanneer een gebruiker naar het pattern (ingesteld in de deze file) navigeert (deze moet je aanpassen):

De configuratie wordt geladen uit pageStructures/... (jouw file met de nodige data)
De benodigde data voor het formulier wordt opgehaald:
- main
- tabs
- amountOfColumns
- fields

De rol van de ingelogde gebruiker wordt gecontroleerd. Enkel gebruikers met een toegestane rol krijgen toegang tot het formulier.
Gebruikers zonder toestemming worden doorgestuurd naar panel/records of een naar jouw gekozen plaats.

Configureerbare velden
- pattern
- Allowed roles ($allowed array)
- structure (include hier de file uit pagestructures)
- Breadcrumb label (Bepaalt de tekst die bovenaan in de navigatie wordt weergegeven.) vb.:
```PHP
'breadcrumb' => [
  [
    'label' => str_replace("{type}", "About", t("libis.solis.nav.add.record")),
    'link' => 'records/example/toevoegen'
  ]
]
```
In dit voorbeeld wordt "About" gebruikt als recordtype. Vervang deze waarde door de gewenste naam.

<br/>

### Detail (edit) pagina
Deze file dient voor de detail gegevens van 1 record van dit recordtype voor het bekijken of aanpassen van dit record door te geven aan het scherm. [Voorbeeld](https://github.com/libis/kirby-plugin-solis/blob/main/views/example/exampleRecord.php)

Werking
Wanneer een gebruiker naar het pattern (ingesteld in de deze file) navigeert (deze moet je aanpassen):

De taal die gevraagd wordt wordt opgehaald uit de url (als deze is opgegeven en anders nl (dit kan aangepast worden).
Het item wordt via de api opgevraagd. Pas de request aan naar het juiste type / diepte / ....

De configuratie wordt geladen uit pageStructures/... (jouw file met de nodige data)
De benodigde data voor het formulier wordt opgehaald:
- main
- tabs
- amountOfColumns
- fields

Configureerbare velden
- pattern
- default taal
- query (api request)
- structure (include hier de file uit pagestructures)
- Breadcrumb label (Bepaalt de tekst die bovenaan in de navigatie wordt weergegeven.) vb.:
```PHP
'breadcrumb' => [
  [
    'label' => 'About: ' . (isset($item[0]->tag->tag_name) ? $item[0]->tag->tag_name : ''),
    'link' => 'records/example/toevoegen'
  ]
]
```
In dit voorbeeld wordt de tag naam van de bekomen data via api gebruikt gebruikt als recordtype. Vervang deze waarde door de gewenste waarde.

<br/>

#### List pagina
Deze file dient voor een lijst te tonen van alle records die van dit type bestaan. [Voorbeeld](https://github.com/libis/kirby-plugin-solis/blob/main/views/example/exampleRecords.php)

Werking
Wanneer een gebruiker naar het pattern (ingesteld in de deze file) navigeert (deze moet je aanpassen):

De configuratie wordt geladen uit pageStructures/... (jouw file met de nodige data)
De benodigde data voor het formulier wordt opgehaald:
- main
- tabs
- title and info selecctor
- user data

Configureerbare velden
- pattern
- structure (include hier de file uit pagestructures)
- Breadcrumb label (Bepaalt de tekst die bovenaan in de navigatie wordt weergegeven.) vb.:
```PHP
'breadcrumb' => [
  [
    'label' => "About " . t("libis.solis.records"),
    'link' => 'records/example/toevoegen'
  ]
]
```
In dit voorbeeld wordt "About" gebruikt als recordtype. Vervang deze waarde door de gewenste naam.

<br/>

#### Codetabellen
Ook hier zijn codetabellen een uitzondering op de opbouw van de pagina's. [Voorbeeld](https://github.com/libis/kirby-plugin-solis/tree/main/views/codeTables) Er is namelijk maar 1 map voor alle codetabellen en deze map bevat maar 1 file.

Werking
Wanneer een gebruiker naar het pattern codetables gaat:

De taal die gevraagd wordt wordt opgehaald uit de url (als deze is opgegeven en anders nl (dit kan aangepast worden).

De configuratie wordt geladen uit pageStructures/... (jouw file met de nodige data)
De benodigde data voor het formulier wordt opgehaald:
- main
- lijst van de codetables

Er wordt door de codetables geloopt.
Per codetables type wordt een request gedaan om alle opties op te halen. Pas de request aan indien nodig

Configureerbare velden
- default taal
- structure (include hier de file uit pagestructures)
- Breadcrumb label (Bepaalt de tekst die bovenaan in de navigatie wordt weergegeven.) vb.:
```PHP
'breadcrumb' => [
  [
    'label' => t("libis.solis.codeTables"),
    'link' => 'records/example/toevoegen'
  ]
]
```
In dit voorbeeld wordt "Codetabbelen' vertaald in de juiste gevraagde taal. Pas aan naar wens.

<br/>

### area views
Maak 1 php file aan genaamd views.php. Deze file zal een array returnen met alle php files uit de views map. Elke file voeg je toe op volgende manier:
``` php
require __DIR__ . '/../views/items/exampleRecords.php',
```
Een voorbeeld van deze return array vind je in [area-views/views.php](https://github.com/libis/kirby-plugin-solis/blob/main/area-views/views.php)

<br/>

### Data-processing
Maak per type een file aan. In deze file zal je 3 routes vinden (post, put, delete).

Kopieer het [voorbeeld](https://github.com/libis/kirby-plugin-solis/blob/main/data-processing/example.php) en pas de gegevens zoals de url aan.
