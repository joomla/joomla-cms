# php-stemmer

PHP5 native implementation of Snowball stemmer
http://snowball.tartarus.org/

Accept only UTF-8

* [Languages](#languages)
* [Installation](#installation)
* [Usage](#usage)

Languages
------------
Available : 
- English
- French
- German
- Italian
- Spanish
- Portuguese
- Romanian
- Dutch
- Swedish
- Norwegian
- Danish

Next :
- Russian

Soon : 
 - Finnish 

Installation
------------

Require [`wamania/php-stemmer`](https://packagist.org/packages/wamania/php-stemmer)
into your `composer.json` file:


``` json
{
    "require": {
        "wamania/php-stemmer": "dev-master"
    }
}
```

Usage
-----

In your code:

``` php
use Wamania\Snowball\French;

$stemmer = new French();
$stem = $stemmer->stem('anticonstitutionnellement');
```
