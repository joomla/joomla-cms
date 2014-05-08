# The Utilities Package

## Using ArrayHelper

### toInteger

```php
use Joomla\Utilities\ArrayHelper;

$input = array(
    "width" => "100",
    "height" => "200xxx",
    "length" => "10.3"
);
$result = ArrayHelper::toInteger($input);
var_dump($result);
```
Result:
```
array(3) {
  'width' =>
  int(100)
  'height' =>
  int(200)
  'length' =>
  int(10)
}
```

### toObject

```php
use Joomla\Utilities\ArrayHelper;

class Book {
    public $name;
    public $author;
    public $genre;
    public $rating;
}
class Author {
    public $name;
    public $born;
}
$input = array(
    "name" => "The Hitchhiker's Guide to the Galaxy",
    "author" => array(
        "name" => "Douglas Adams",
        "born" => 1952,
        "died" => 2001),
    "genre" => "comic science fiction",
    "rating" => 10
);
$book = ArrayHelper::toObject($input, 'Book');
var_dump($book);
```
Result:
```
class Book#1 (4) {
  public $name =>
  string(36) "The Hitchhiker's Guide to the Galaxy"
  public $author =>
  class Book#2 (6) {
    public $name =>
    string(13) "Douglas Adams"
    public $author =>
    NULL
    public $genre =>
    NULL
    public $rating =>
    NULL
    public $born =>
    int(1952)
    public $died =>
    int(2001)
  }
  public $genre =>
  string(21) "comic science fiction"
  public $rating =>
  int(10)
}
```

### toString

```php
use Joomla\Utilities\ArrayHelper;

$input = array(
    "fruit" => "apple",
    "pi" => 3.14
);
echo ArrayHelper::toString($input);
```
Result:
```
fruit="apple" pi="3.14"
```

### fromObject

```php
use Joomla\Utilities\ArrayHelper;

class Book {
    public $name;
    public $author;
    public $genre;
    public $rating;
}
class Author {
    public $name;
    public $born;
}

$book = new Book();
$book->name = "Harry Potter and the Philosopher's Stone";
$book->author = new Author();
$book->author->name = "J.K. Rowling";
$book->author->born = 1965;
$book->genre = "fantasy";
$book->rating = 10;

$array = ArrayHelper::fromObject($book);
var_dump($array);
```
Result:
```
array(4) {
  'name' =>
  string(40) "Harry Potter and the Philosopher's Stone"
  'author' =>
  array(2) {
    'name' =>
    string(12) "J.K. Rowling"
    'born' =>
    int(1965)
  }
  'genre' =>
  string(7) "fantasy"
  'rating' =>
  int(10)
}
```

### getColumn

```php
use Joomla\Utilities\ArrayHelper;

$rows = array(
    array("name" => "John", "age" => 20),
    array("name" => "Alex", "age" => 35),
    array("name" => "Sarah", "age" => 27)
);
$names = ArrayHelper::getColumn($rows, 'name');
var_dump($names);
```
Result:
```
array(3) {
  [0] =>
  string(4) "John"
  [1] =>
  string(4) "Alex"
  [2] =>
  string(5) "Sarah"
}
```

### getValue
```php
use Joomla\Utilities\ArrayHelper;

$city = array(
    "name" => "Oslo",
    "country" => "Norway"
);

// Prints 'Oslo'
echo ArrayHelper::getValue($city, 'name');

// Prints 'unknown mayor' (no 'mayor' key is found in the array)
echo ArrayHelper::getValue($city, 'mayor', 'unknown mayor');
```

### invert

```php
use Joomla\Utilities\ArrayHelper;

$input = array(
    'New' => array('1000', '1500', '1750'),
    'Used' => array('3000', '4000', '5000', '6000')
);
$output = ArrayHelper::invert($input);
var_dump($output);
```
Result:
```
array(7) {
  [1000] =>
  string(3) "New"
  [1500] =>
  string(3) "New"
  [1750] =>
  string(3) "New"
  [3000] =>
  string(4) "Used"
  [4000] =>
  string(4) "Used"
  [5000] =>
  string(4) "Used"
  [6000] =>
  string(4) "Used"
}
```


### isAssociative

```php
use Joomla\Utilities\ArrayHelper;

$user = array("id" => 46, "name" => "John");
echo ArrayHelper::isAssociative($user) ? 'true' : 'false'; // true

$letters = array("a", "b", "c");
echo ArrayHelper::isAssociative($letters) ? 'true' : 'false'; // false
```

### pivot

```php
use Joomla\Utilities\ArrayHelper;

$movies = array(
    array('year' => 1972, 'title' => 'The Godfather'),
    array('year' => 2000, 'title' => 'Gladiator'),
    array('year' => 2000, 'title' => 'Memento'),
    array('year' => 1964, 'title' => 'Dr. Strangelove')
);
$pivoted = ArrayHelper::pivot($movies, 'year');
var_dump($pivoted);
```
Result:
```
array(3) {
  [1972] =>
  array(2) {
    'year' =>
    int(1972)
    'title' =>
    string(13) "The Godfather"
  }
  [2000] =>
  array(2) {
    [0] =>
    array(2) {
      'year' =>
      int(2000)
      'title' =>
      string(9) "Gladiator"
    }
    [1] =>
    array(2) {
      'year' =>
      int(2000)
      'title' =>
      string(7) "Memento"
    }
  }
  [1964] =>
  array(2) {
    'year' =>
    int(1964)
    'title' =>
    string(15) "Dr. Strangelove"
  }
}
```

### sortObjects

```php
use Joomla\Utilities\ArrayHelper;

$members = array(
    (object) array('first_name' => 'Carl', 'last_name' => 'Hopkins'),
    (object) array('first_name' => 'Lisa', 'last_name' => 'Smith'),
    (object) array('first_name' => 'Julia', 'last_name' => 'Adams')
);
$sorted = ArrayHelper::sortObjects($members, 'last_name', 1);
var_dump($sorted);
```
Result:
```
array(3) {
  [0] =>
  class stdClass#3 (2) {
    public $first_name =>
    string(5) "Julia"
    public $last_name =>
    string(5) "Adams"
  }
  [1] =>
  class stdClass#1 (2) {
    public $first_name =>
    string(4) "Carl"
    public $last_name =>
    string(7) "Hopkins"
  }
  [2] =>
  class stdClass#2 (2) {
    public $first_name =>
    string(4) "Lisa"
    public $last_name =>
    string(5) "Smith"
  }
}
```

### arrayUnique
```php
use Joomla\Utilities\ArrayHelper;

$names = array(
    array("first_name" => "John", "last_name" => "Adams"),
    array("first_name" => "John", "last_name" => "Adams"),
    array("first_name" => "John", "last_name" => "Smith"),
    array("first_name" => "Sam", "last_name" => "Smith")
);
$unique = ArrayHelper::arrayUnique($names);
var_dump($unique);
```
Result:
```
array(3) {
  [0] =>
  array(2) {
    'first_name' =>
    string(4) "John"
    'last_name' =>
    string(5) "Adams"
  }
  [2] =>
  array(2) {
    'first_name' =>
    string(4) "John"
    'last_name' =>
    string(5) "Smith"
  }
  [3] =>
  array(2) {
    'first_name' =>
    string(3) "Sam"
    'last_name' =>
    string(5) "Smith"
  }
}
```

## Installation via Composer

Add `"joomla/utilities": "dev-master"` to the require block in your composer.json, make sure you have `"minimum-stability": "dev"` and then run `composer install`.

```json
{
  "require": {
    "joomla/utilities": "dev-master"
  },
  "minimum-stability": "dev"
}
```

Alternatively, you can simply run the following from the command line:

```sh
composer init --stability="dev"
composer require joomla/utilities "dev-master"
```
