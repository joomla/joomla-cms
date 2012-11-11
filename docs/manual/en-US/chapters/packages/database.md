## The Database Package

### Introduction

The *Database* package is designed to manage the operations of data
management through the use of a generic database engine.

### Iterating on results

The `JDatabaseIterator` class allows iteration over
database results

```php
$dbo = JFactory::getDbo();
$iterator = $dbo->setQuery($dbo->getQuery(true)->select('*')->from('#__content'))->getIterator();
foreach ($iterator as $row)
{
    // Deal with $row
}
```

It allows also to count the results.

```php
$count = count($iterator);
```
