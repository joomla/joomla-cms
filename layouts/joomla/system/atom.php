<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;

/* @var $document \Joomla\CMS\Document\HtmlDocument */
/* @var $displayData [] */
extract($displayData);

$app   = Factory::getApplication();

// Gets and sets timezone offset from site configuration
$tz  = new \DateTimeZone($app->get('offset'));
$now = Factory::getDate();
$now->setTimeZone($tz);

$url = Uri::getInstance()->toString(array('scheme', 'user', 'pass', 'host', 'port'));
$syndicationURL = Route::_('&format=feed&type=atom');

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

$feed = "<feed xmlns=\"http://www.w3.org/2005/Atom\" ";

if ($document->getLanguage() != '')
{
	$feed .= " xml:lang=\"" . $document->getLanguage() . "\"";
}

$feed .= ">\n";
$feed .= "	<title type=\"text\">" . $feed_title . "</title>\n";
$feed .= "	<subtitle type=\"text\">" . htmlspecialchars($document->getDescription(), ENT_COMPAT, 'UTF-8') . "</subtitle>\n";

if (!empty($document->category))
{
	if (\is_array($document->category))
	{
		foreach ($document->category as $cat)
		{
			$feed .= "	<category term=\"" . htmlspecialchars($cat, ENT_COMPAT, 'UTF-8') . "\" />\n";
		}
	}
	else
	{
		$feed .= "	<category term=\"" . htmlspecialchars($document->category, ENT_COMPAT, 'UTF-8') . "\" />\n";
	}
}

$feed .= "	<link rel=\"alternate\" type=\"text/html\" href=\"" . $url . "\">\n";
$feed .= "	<id>" . str_replace(' ', '%20', $document->getBase()) . "</id>\n";
$feed .= "	<updated>" . htmlspecialchars($now->toISO8601(true), ENT_COMPAT, 'UTF-8') . "</updated>\n";

if ($document->editor != '')
{
	$feed .= "	<author>\n";
	$feed .= "		<name>" . $document->editor . "</name>\n";

	if ($document->editorEmail != '')
	{
		$feed .= "		<email>" . htmlspecialchars($document->editorEmail, ENT_COMPAT, 'UTF-8') . "</email>\n";
	}

	$feed .= "	</author>\n";
}

$versionHtmlEscaped = '';

if ($app->get('MetaVersion', 0))
{
	$minorVersion       = Version::MAJOR_VERSION . '.' . Version::MINOR_VERSION;
	$versionHtmlEscaped = ' version="' . htmlspecialchars($minorVersion, ENT_COMPAT, 'UTF-8') . '"';
}

$feed .= "	<generator uri=\"https://www.joomla.org\"" . $versionHtmlEscaped . ">" . $document->getGenerator() . "</generator>\n";
$feed .= "	<link rel=\"self\" type=\"application/atom+xml\" href=\"" . str_replace(' ', '%20', $url . $syndicationURL) . "\">\n";

for ($i = 0, $count = \count($document->items); $i < $count; $i++)
{
	$itemlink = $document->items[$i]->link;

	if (preg_match('/[\x80-\xFF]/', $itemlink))
	{
		$itemlink = implode('/', array_map('rawurlencode', explode('/', $itemlink)));
	}

	$feed .= "	<entry>\n";
	$feed .= "		<title>" . htmlspecialchars(strip_tags($document->items[$i]->title), ENT_COMPAT, 'UTF-8') . "</title>\n";
	$feed .= "		<link rel=\"alternate\" type=\"text/html\" href=\"" . $url . $itemlink . "\">\n";

	if ($document->items[$i]->date == '')
	{
		$document->items[$i]->date = $now->toUnix();
	}

	$itemDate = Factory::getDate($document->items[$i]->date);
	$itemDate->setTimeZone($tz);
	$feed .= "		<published>" . htmlspecialchars($itemDate->toISO8601(true), ENT_COMPAT, 'UTF-8') . "</published>\n";
	$feed .= "		<updated>" . htmlspecialchars($itemDate->toISO8601(true), ENT_COMPAT, 'UTF-8') . "</updated>\n";

	if (empty($document->items[$i]->guid))
	{
		$itemGuid = str_replace(' ', '%20', $url . $itemlink);
	}
	else
	{
		$itemGuid = htmlspecialchars($document->items[$i]->guid, ENT_COMPAT, 'UTF-8');
	}

	$feed .= "		<id>" . $itemGuid . "</id>\n";

	if ($document->items[$i]->author != '')
	{
		$feed .= "		<author>\n";
		$feed .= "			<name>" . htmlspecialchars($document->items[$i]->author, ENT_COMPAT, 'UTF-8') . "</name>\n";

		if (!empty($document->items[$i]->authorEmail))
		{
			$feed .= "			<email>" . htmlspecialchars($document->items[$i]->authorEmail, ENT_COMPAT, 'UTF-8') . "</email>\n";
		}

		$feed .= "		</author>\n";
	}

	if (!empty($document->items[$i]->description))
	{
		$feed .= "		<summary type=\"html\">" . htmlspecialchars($document->_relToAbs($document->items[$i]->description), ENT_COMPAT, 'UTF-8') . "</summary>\n";
		$feed .= "		<content type=\"html\">" . htmlspecialchars($document->_relToAbs($document->items[$i]->description), ENT_COMPAT, 'UTF-8') . "</content>\n";
	}

	if (!empty($document->items[$i]->category))
	{
		if (\is_array($document->items[$i]->category))
		{
			foreach ($document->items[$i]->category as $cat)
			{
				$feed .= "		<category term=\"" . htmlspecialchars($cat, ENT_COMPAT, 'UTF-8') . "\" />\n";
			}
		}
		else
		{
			$feed .= "		<category term=\"" . htmlspecialchars($document->items[$i]->category, ENT_COMPAT, 'UTF-8') . "\" />\n";
		}
	}

	if ($document->items[$i]->enclosure != null)
	{
		$feed .= "		<link rel=\"enclosure\" href=\"" . $document->items[$i]->enclosure->url . "\" type=\""
			. $document->items[$i]->enclosure->type . "\"  length=\"" . $document->items[$i]->enclosure->length . "\">\n";
	}

	$feed .= "	</entry>\n";
}

$feed .= "</feed>\n";

echo $feed;
