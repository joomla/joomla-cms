## The Profiler Package

The Joomla Platform provides you a simple utility class called `JProfiler` to profile the time that it takes to do certain tasks or reach various milestones as your extension runs.

To use the profiler we create an instance. There is a global instance of the profiler that can be used anywhere.

```php
$profiler = JProfiler::getInstance();
```

Note that if you use this, it could already have been used by the application or its sub-elements.  You can create a local instance for testing an individual plugin, component or module by passing a label to the method like this:

```php
$profiler = JProfiler::getInstance('Notes');
```

The profiler class will record the time that it was created.  To mark milestones in the execution in your code, you can use the mark method, passing it a label to describe where it happened.

```php
$profiler = JProfiler::getInstance('Notes');

$profiler->mark('Start');

// Execute some code

$profiler->mark('Finish');
```

You can mark any number of times but it is always a good idea to mark the start and the finish even if you have intermediate steps.

When you have finished, you can output the results using the `getBuffer` method.  This returns an array of the marks you have made.

```php
// Execute previous code
$profiler->mark('Finish');
$buffer = $profile->getBuffer();
echo implode('<br />', $buffer);
```

The output could look something like the following:

```
Notes 0.015 seconds (+0.015); 0.96 MB (+0.960) - Start
Notes 1.813 seconds (+1.798); 6.24 MB (+5.280) - Finished
```

You can see each line is qualified by the label you used when you created the profiler object, and then the label you used for the mark.  Following that is the time difference from when the profiler object was created down to the millisecond level.  Lastly is the amount of memory that is being usage by PHP.