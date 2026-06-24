# Kirby Plugin dat connecteerd met de SOLIS data

> Show | edit | delete data van SOLIS
> Versie 1.0 

---

## ✨ Features

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

## Project structuur
Deze plugin weet uitzichzelf niet welke entititeiten een pagina krijgen en wat er moet gebeuren voordat de data naar SOLIS wordt gestuurd. Het is dus belangrijk dat je in `/site/plugins` een map `solis-records` maakt. In deze map maak je minstens volgende structuur. Bij gebruik in een docker image, mount niet de volledige plugin maar de onderstaande vermelden mappen/files.

```
site/
└── plugins/
    └── solis-records/
        ├── data-processing
        ├── pageStructures
        ├── views
        └── index.php
```

### PaginaStructuur
Per pagina type maak je een file aan in de map `pageStructures`. Niet alle entiteiten 


#### Entiteit


#### CodeTabel

#### Mogelijke velden

### Views

### Data afhandeling
