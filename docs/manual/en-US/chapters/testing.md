## Testing

This document is about creating tests for the Joomla Platform.

## PHP Unit Tests

This section provides some useful information about making PHP unit
tests.

It is important to note that unit test code must follow the same coding
standards as the rest of the platform. To that end, code style must be
observed and DocBlocks fully completed. The `@since` tag must be
included when you add a new, full test (not necessary for test stubs
that are marked as incomplete).

### Accessing and Invoking Protected Properties and Methods

Starting in PHP 5 there are facilities for reverse engineering classes
and objects which now make it much easier to test protected methods and
to modify and verify the values of protected properties and methods. The
Joomla! test infrastructure includes a helper class that can be used to
read, modify and invoke protected and private properties and methods.

```php
/**
 * Tests the JClass::__construct method
 *
 * @return  void
 *
 * @since   11.3
 */
public function test__construct()
{
	$object = new JMyClass('foo');

	// For the purposes of this example, we are assuming that 'name' is a protected class property
	// that is set by an argument passed into the class constructor.
	$this->assertThat(
		TestReflection::getValue($object, 'name'),
		$this->equalTo('foo'),
		'Tests that the protected name property is set by the constructor.'
	);
}

To gain access to a protected method you might do something add a method
like the following example to your inspector class:

/**
 * Test that calls a protected method called 'hidden'
 *
 * @return  void
 *
 * @since   11.3
 */
public function hidden()
{
	$object = new JMyClass('foo');

	$this->assertThat(
		TestReflection::invoke($object, 'hidden', 'arg1'),
		$this->equalTo('result1'),
		'This test asserts that $object->hidden("arg1") will return "result1"'
	);
}
```

> **Note**
>
> Unit tests should always observe the Joomla Platform Coding Standards.
> Ensure the code is well formatted and that full DocBlocks are
> provided. Most importantly, ensure the @since tag reflects the version
> the test was added.
