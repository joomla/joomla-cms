## The Feed Package

### Introduction

The *Feed* package is designed to provide a straightforward way to manage interactions with RSS and atom feeds. It supports feed namespacing.

### Accessing a feed

The feed package accesses an individual feed using a factory followed by the getFeed() method which takes the feed's uri as its only argument. Calls to the getFeed() method should be enclosed in a try block since they will throw runtime exceptions if either connecting with or retrieving the feed fails.


```php
try
{
	$feed = new JFeedFactory;
	$feedDoc = $feed->getFeed(http://feeds.joomla.org/JoomlaAnnouncements);
}
catch (RunTimeException $e)
{
	$msg = JText::_('ERROR_FEED_NOT_RETRIEVED');
}
```


### The Feed Class and Child Classes

The factory class provides access to the feed class, its children, and their protected properties.

The main class is the feed class which includes properties describing the feed as a whole: the feed uri, title, date of most recent update, description, categories and contributors. Magic get and set methods allow access to these and other methods allow more fine grained manipulation. The same is true for the child classes.

The entry class, JFeedEntry, manages data about individual entries in the feed. 
The person class JFeedPerson, manages data about individual persons connected with the feed, typically authors, contributors or the person responsible for the feed as a whole.
The link class, JFeedLink, manages data about individual links in a feed. Among other things it is used to construct html links to entries. 

```php
	// Show the feed description
	echo $feedDoc->description;

	// Create a link to the ith item in a feed.
	echo '<a href="' . $feedDoc[$i]->uri . '" target="_blank">'
		. $feedDoc[$i]->title . '</a>';

```

JFeedParser creates an XMLReader to manage parsing feed objects. Rss and atom are supported. 

This example shows a simple example of how a complete feed might be rendered. Always keep in mind that not all feeds will support all elements, which means that the existence of an element should be checked for before attempting to use it. Some differences between Atom and RSS (such as use of guid) can also be incorporated by checking for their presence.

You also may want to use JHtmlString::truncate or JHtmlString::truncateComplex to limit the number of characters rendered and JFilterOutput::stripImages(), JFilterOutput::stripIframes or other filtering options.

```php

if (isset($feedDoc->title))
{
	echo str_replace('&apos;', "'", $feedDoc->title);
}

if (isset($feedDoc->description))
{
	echo str_replace('&apos;', "'", $feedDoc->description);
}
if (isset($feedDoc->image))
{
	echo  '<img src="' . $feedDoc->image . '"/>';
}
if (!empty($this->rssDoc[0]))
{
	// Set the number of entries to display
	$numentries = 5;
	for ($i = 0; $i < $numentries; $i++)
	{
		if (!empty($this->feedDoc[$i]->uri))
		{
			echo '<a href="' . $feedDoc[$i]->uri . '" target="_blank">'
			. $feedDoc[$i]->title . '</a>';
		}
		if (!empty($feedDoc[$i]->content))
		{
			echo $feedDoc[$i]->content;
		}
	}
}
```php


## Namespacing Support
Namespacing in feeds is used to add specialized elements to a feed. Some are widely used but individual feeds may also have customized namespacing. JFeed supports  dependency injection for namespacing. Currently media and itunes support is implemented. 

#### More Information

More information on feed related topics can be found at:

[Atom Specification](http://www.atomenabled.org/developers/syndication/)
[RSS Specification](http://cyber.law.harvard.edu/rss/rss.html)
[W3 information on name spaces](http://feed2.w3.org/docs/howto/declare_namespaces.html)
[Extending RSS with Namespaces](http://www.disobey.com/detergent/2002/extendingrss2/)
[iTunes Namespace Specification](http://www.apple.com/itunes/podcasts/specs.html)
[Media Namespace specifications](http://video.search.yahoo.com/mrss)
[XMLReader Documentation](http://php.net/manual/en/book.xmlreader.php)
