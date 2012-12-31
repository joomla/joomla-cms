## The Openstreetmap Package

### Using the Openstreetmap Package
The intention of the Openstreetmap package is to provide an easy straightforward interface to work with OpenStreetMap. This is based on the version 0.6 of the OpenStreetMap API. You can find more information about the OpenStreetMap API at [http://wiki.openstreetmap.org/wiki/API_v0.6](http://wiki.openstreetmap.org/wiki/API_v0.6) .
JOpenstreetmap is built upon JOAuth1Client package which provides OAuth 1.0 security infrastructure for the communications. JHttp package is also used as an easy way for the non-secure information exchanges.
### Initiating JOpenstreetmap
Initiating JOpenstreetmap is just a single line of code:

```php
$osm = new JOpenstreetmap();
```

This creates basic JOpenstreetmap object which can access publically available GET methods.
But when you want to send data or get private data, you need to use JOpenstreetmapOauth object too.
```php
$key = "your_key";
$secret = "your_secret";

$option = new JRegistry;
$option->set('consumer_key', $key);
$option->set('consumer_secret', $secret);
$option->set('sendheaders', true);

$oauth = new JOpenstreetmapOauth($option);
$oauth->authenticate();

$osm = new JOpenstreetmap($oauth);
```

To obtain a key and secret, you have to obtain an account at OpenStreetMap. Through your account you need to [register](http://www.openstreetmap.org/user/username/oauth_clients/new) your application along with a callback URL.

### Accessing JOpenstreetmap API
This API will do all types of interactions with OpenStreetMap API. This has been categorized in to 5 main  sections: Changeset, Element, Gps, Info and User. All those are inherited from JOpenstreetmapObject and can be initiated through magic __get method of JOpenstreetmap class. Methods contained in each type of object are closely relate to the Openstreetmap API calls.

### General Usage
For an example, to get an element with a known identifier you need to just add following two lines additionally after creating `$osm` .
```php
$element = $osm->elements;
$result = $element->readElement('node', 123);

// To view SimpleXMLElement
print_r($result);
```
For sending information to server you must use OAuth authentication. Following is a complete sample application of creating a new changeset. Later you can use your own changeset to add elements you want.
```php
$key = "your_key";
$secret = "your_secret";

$option = new JRegistry;
$option->set('consumer_key', $key);
$option->set('consumer_secret', $secret);
$option->set('sendheaders', true);

$oauth = new JOpenstreetmapOauth($option);
$oauth->authenticate();

$osm = new JOpenstreetmap($oauth);

$changeset= $osm->changesets;

$changesets = array
(
"comment" => "My First Changeset",
"created_by" => "JOpenstreetmap"
);
$result = $changeset->createChangeset($changesets);

// Returned value contains  the identifier of new changeset
print_r($result);
```
### More Information
Following resources contain more information: [OpenStreetMap API](http://wiki.openstreetmap.org/wiki/API)