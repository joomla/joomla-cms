## The Database Package

### Introduction

The *Database* package is designed to manage the operations of data
management through the use of a generic database engine.

### Escaping strings

Strings must be escaped before using them in queries (never trust any variable input, even if it comes from a previous database query from your own data source). This can be done using the `escape` and the `quote` method.

The `escape` method will generally backslash unsafe characters (unually quote characters but it depends on the database engine). It also allows for optional escaping of additional characters (such as the underscore or percent when used in conjunction with a `LIKE` clause).

The `quote` method will escape a string and wrap it in quotes, however, the escaping can be turned off which is desirable in some situations. The `quote` method will also accept an array of strings (added in 12.3) and return an array quoted and escaped (unless turned off) string.

```php
function search($title)
{
	// Get the database driver from the factory, or by some other suitable means.
	$db = JFactory::getDbo();

	// Search for an exact match of the title, correctly sanitising the untrusted input.
	$sql1 = 'SELECT * FROM #__content WHERE title = ' . $db->quote($title);
	
	// Special treatment for a LIKE clause.
	$search = $db->quote($db->escape($title, true) . '%', false);
	$sql2 = 'SELECT * FROM #__content WHERE title LIKE ' . $search;
	
	// 
	if (is_array($title))
	{
		$sql3 = 'SELECT * FROM #__content WHERE title IN ('
			. implode(',', $db->quote($title)) . ')';
	}
	
	// Do the database calls.
}
```

In the first case, the title variable is simply escaped and quoted. Any quote characters in the title string will be prepended with a backslash and the whole string will be wrapped in quotes.

In the second case, the example shows how to treat a search string that will be used in a `LIKE` clause. In this case, the title variable is manually escaped using `escape` with a second argument of `true`. This will force other special characters to be escaped (otherwise you could set youself up for serious performance problems if the user includes too many wildcards). Then, the result is passed to the `quote` method but escaping is turned off (because it has already been done manually).

In the third case, the title variable is an array so the whole array can be passed to the `quote` method (this saves using a closure and a )

Shorthand versions are  available the these methods: 
* `q` can be used instead of `quote`
* `e` can be used instead of `escape`

These shorthand versions are also available when using the `JDatabaseQuery` class.

### Iterating on results

The `JDatabaseIterator` class allows iteration over
database results

```php
$dbo = JFactory::getDbo();
$iterator = $dbo->setQuery(
	$dbo->getQuery(true)->select('*')->from('#__content')
)->getIterator();
foreach ($iterator as $row)
{
    // Deal with $row
}
```

It allows also to count the results.

```php
$count = count($iterator);
```
