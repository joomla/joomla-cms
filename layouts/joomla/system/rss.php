<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/* @var $document \Joomla\CMS\Document\HtmlDocument */
/* @var $displayData [] */
extract($displayData);

$app = Factory::getApplication();
$tz  = new \DateTimeZone($app->get('offset'));

// If the last build date from the document isn't a Date object, create one
if (!($document->lastBuildDate instanceof Date))
{
	// Gets and sets timezone offset from site configuration
	$document->lastBuildDate = Factory::getDate();
	$document->lastBuildDate->setTimeZone(new \DateTimeZone($app->get('offset')));
}

$url = Uri::getInstance()->toString(array('scheme', 'user', 'pass', 'host', 'port'));
$syndicationURL = Route::_('&format=feed&type=rss');

$title = $document->getTitle();

if ($app->get('sitename_pagetitles', 0) == 1)
{
	$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $document->getTitle());
}
elseif ($app->get('sitename_pagetitles', 0) == 2)
{
	$title = Text::sprintf('JPAGETITLE', $document->getTitle(), $app->get('sitename'));
}

$feed_title = htmlspecialchars($title, ENT_COMPAT, 'UTF-8');

$documentlink = $document->getLink();

if (preg_match('/[\x80-\xFF]/', $documentlink))
{
	$documentlink = implode('/', array_map('rawurlencode', explode('/', $documentlink)));
}

$feed = "<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n";
$feed .= "	<channel>\n";
$feed .= "		<title>" . $feed_title . "</title>\n";
$feed .= "		<description><![CDATA[" . $document->getDescription() . "]]></description>\n";
$feed .= "		<link>" . str_replace(' ', '%20', $url . $documentlink) . "</link>\n";
$feed .= "		<lastBuildDate>" . htmlspecialchars($document->lastBuildDate->toRFC822(true), ENT_COMPAT, 'UTF-8') . "</lastBuildDate>\n";
$feed .= "		<generator>" . $document->getGenerator() . "</generator>\n";
$feed .= "		<atom:link rel=\"self\" type=\"application/rss+xml\" href=\"" . str_replace(' ', '%20', $url . $syndicationURL) . "\"/>\n";

if ($document->image != null)
{
	$feed .= "		<image>\n";
	$feed .= "			<url>" . $document->image->url . "</url>\n";
	$feed .= "			<title>" . htmlspecialchars($document->image->title, ENT_COMPAT, 'UTF-8') . "</title>\n";
	$feed .= "			<link>" . str_replace(' ', '%20', $document->image->link) . "</link>\n";

	if ($document->image->width != '')
	{
		$feed .= "			<width>" . $document->image->width . "</width>\n";
	}

	if ($document->image->height != '')
	{
		$feed .= "			<height>" . $document->image->height . "</height>\n";
	}

	if ($document->image->description != '')
	{
		$feed .= "			<description><![CDATA[" . $document->image->description . "]]></description>\n";
	}

	$feed .= "		</image>\n";
}

if ($document->getLanguage() !== '')
{
	$feed .= "		<language>" . $document->getLanguage() . "</language>\n";
}

if ($document->copyright != '')
{
	$feed .= "		<copyright>" . htmlspecialchars($document->copyright, ENT_COMPAT, 'UTF-8') . "</copyright>\n";
}

if ($document->editorEmail != '')
{
	$feed .= "		<managingEditor>" . htmlspecialchars($document->editorEmail, ENT_COMPAT, 'UTF-8') . ' ('
		. htmlspecialchars($document->editor, ENT_COMPAT, 'UTF-8') . ")</managingEditor>\n";
}

if ($document->webmaster != '')
{
	$feed .= "		<webMaster>" . htmlspecialchars($document->webmaster, ENT_COMPAT, 'UTF-8') . "</webMaster>\n";
}

if ($document->pubDate != '')
{
	$pubDate = Factory::getDate($document->pubDate);
	$pubDate->setTimeZone($tz);
	$feed .= "		<pubDate>" . htmlspecialchars($pubDate->toRFC822(true), ENT_COMPAT, 'UTF-8') . "</pubDate>\n";
}

if (!empty($document->category))
{
	if (\is_array($document->category))
	{
		foreach ($document->category as $cat)
		{
			$feed .= "		<category>" . htmlspecialchars($cat, ENT_COMPAT, 'UTF-8') . "</category>\n";
		}
	}
	else
	{
		$feed .= "		<category>" . htmlspecialchars($document->category, ENT_COMPAT, 'UTF-8') . "</category>\n";
	}
}

if ($document->docs != '')
{
	$feed .= "		<docs>" . htmlspecialchars($document->docs, ENT_COMPAT, 'UTF-8') . "</docs>\n";
}

if ($document->ttl != '')
{
	$feed .= "		<ttl>" . htmlspecialchars($document->ttl, ENT_COMPAT, 'UTF-8') . "</ttl>\n";
}

if ($document->rating != '')
{
	$feed .= "		<rating>" . htmlspecialchars($document->rating, ENT_COMPAT, 'UTF-8') . "</rating>\n";
}

if ($document->skipHours != '')
{
	$feed .= "		<skipHours>" . htmlspecialchars($document->skipHours, ENT_COMPAT, 'UTF-8') . "</skipHours>\n";
}

if ($document->skipDays != '')
{
	$feed .= "		<skipDays>" . htmlspecialchars($document->skipDays, ENT_COMPAT, 'UTF-8') . "</skipDays>\n";
}

for ($i = 0, $count = \count($document->items); $i < $count; $i++)
{
	$itemlink = $document->items[$i]->link;

	if (preg_match('/[\x80-\xFF]/', $itemlink))
	{
		$itemlink = implode('/', array_map('rawurlencode', explode('/', $itemlink)));
	}

	if ((strpos($itemlink, 'http://') === false) && (strpos($itemlink, 'https://') === false))
	{
		$itemlink = str_replace(' ', '%20', $url . $itemlink);
	}

	$feed .= "		<item>\n";
	$feed .= "			<title>" . htmlspecialchars(strip_tags($document->items[$i]->title), ENT_COMPAT, 'UTF-8') . "</title>\n";
	$feed .= "			<link>" . str_replace(' ', '%20', $itemlink) . "</link>\n";

	if (empty($document->items[$i]->guid))
	{
		$feed .= "			<guid isPermaLink=\"true\">" . str_replace(' ', '%20', $itemlink) . "</guid>\n";
	}
	else
	{
		$feed .= "			<guid isPermaLink=\"false\">" . htmlspecialchars($document->items[$i]->guid, ENT_COMPAT, 'UTF-8') . "</guid>\n";
	}

	$feed .= "			<description><![CDATA[" . $document->_relToAbs($document->items[$i]->description) . "]]></description>\n";

	if ($document->items[$i]->authorEmail != '')
	{
		$feed .= '			<author>'
			. htmlspecialchars($document->items[$i]->authorEmail . ' (' . $document->items[$i]->author . ')', ENT_COMPAT, 'UTF-8') . "</author>\n";
	}

	/*
	 * @todo: On hold
	 * if ($document->items[$i]->source!='')
	 * {
	 *   $document.= "			<source>" . htmlspecialchars($document->items[$i]->source, ENT_COMPAT, 'UTF-8') . "</source>\n";
	 * }
	 */

	if (empty($document->items[$i]->category) === false)
	{
		if (\is_array($document->items[$i]->category))
		{
			foreach ($document->items[$i]->category as $cat)
			{
				$feed .= "			<category>" . htmlspecialchars($cat, ENT_COMPAT, 'UTF-8') . "</category>\n";
			}
		}
		else
		{
			$feed .= "			<category>" . htmlspecialchars($document->items[$i]->category, ENT_COMPAT, 'UTF-8') . "</category>\n";
		}
	}

	if ($document->items[$i]->comments != '')
	{
		$feed .= "			<comments>" . htmlspecialchars($document->items[$i]->comments, ENT_COMPAT, 'UTF-8') . "</comments>\n";
	}

	if ($document->items[$i]->date != '')
	{
		$itemDate = Factory::getDate($document->items[$i]->date);
		$itemDate->setTimeZone($tz);
		$feed .= "			<pubDate>" . htmlspecialchars($itemDate->toRFC822(true), ENT_COMPAT, 'UTF-8') . "</pubDate>\n";
	}

	if ($document->items[$i]->enclosure != null)
	{
		$feed .= "			<enclosure url=\"";
		$feed .= $document->items[$i]->enclosure->url;
		$feed .= "\" length=\"";
		$feed .= $document->items[$i]->enclosure->length;
		$feed .= "\" type=\"";
		$feed .= $document->items[$i]->enclosure->type;
		$feed .= "\"/>\n";
	}

	$feed .= "		</item>\n";
}

$feed .= "	</channel>\n";
$feed .= "</rss>\n";

echo $feed;
