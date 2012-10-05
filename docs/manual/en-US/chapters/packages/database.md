The Database Package
====================

Introduction
------------

The *Database* package is designed to manage the operations of data
management through the use of a generic database engine.

Iterating on results
--------------------

The `JDatabaseIterator`{.PHP (HTML)} class allows iteration over
database results

~~~~ {.PHP (HTML)}
$dbo = JFactory::getDbo();
$iterator = $dbo->setQuery($dbo->getQuery(true)->select('*')->from('#__content'))->getIterator();
foreach ($iterator as $row)
{
    // Deal with $row
}
~~~~

It allows also to count the results.

~~~~ {.PHP (HTML)}
$count = count($iterator);
~~~~
