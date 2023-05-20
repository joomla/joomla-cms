~~~ .html
<ul>
    <li>Code block first in file</li>
    <li>doesn't work under some circumstances</li>
</ul>
~~~

As above: checking for bad interractions with the HTML block parser:

~~~ html
<div>
~~~

Some *markdown* `formatting`.

~~~ html
</div>
~~~

Some *markdown*

~~~
<div>
    <html>
~~~

~~~
function test();
~~~

<div markdown="1">
    <html>
        <title>
</div>

<div markdown="1">
~~~
<html>
    <title>
~~~
</div>

Two code blocks with no blank line between them:

~~~
<div>
~~~
~~~
<div>
~~~

Testing *confusion* with markers in the middle:

~~~
<div>~~~</div>
~~~

Testing mixing with title code blocks

~~~
<p>```
```
<p>```
~~~
```
<p>```
~~~
<p>```
```
