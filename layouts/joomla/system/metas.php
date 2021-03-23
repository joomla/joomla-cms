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
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\WebAsset\WebAssetAttachBehaviorInterface;
use Joomla\Utilities\ArrayHelper;

/* @var $document \Joomla\CMS\Document\HtmlDocument */
/* @var $displayData [] */
extract($displayData);

// Convert the tagids to titles
if (isset($document->_metaTags['name']['tags']))
{
	$tagsHelper = new TagsHelper;
	$document->_metaTags['name']['tags'] = implode(', ', $tagsHelper->getTagNames($document->_metaTags['name']['tags']));
}

/** @var \Joomla\CMS\Application\CMSApplication $app */
$app = Factory::getApplication();
$wa  = $document->getWebAssetManager();

// Check for AttachBehavior and web components
foreach ($wa->getAssets('script', true) as $asset)
{
	if ($asset instanceof WebAssetAttachBehaviorInterface)
	{
		$asset->onAttachCallback($document);
	}
}

// Trigger the onBeforeCompileHead event
$app->triggerEvent('onBeforeCompileHead');

// Add Script Options as inline asset
$scriptOptions = $document->getScriptOptions();

if ($scriptOptions)
{
	$prettyPrint = (JDEBUG && \defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : false);
	$jsonOptions = json_encode($scriptOptions, $prettyPrint);
	$jsonOptions = $jsonOptions ? $jsonOptions : '{}';

	$wa->addInlineScript(
		$jsonOptions,
		['name' => 'joomla.script.options', 'position' => 'before'],
		['type' => 'application/json', 'class' => 'joomla-script-options new'],
		['core']
	);
}

// Lock the AssetManager
$wa->lock();

// Get line endings
$lnEnd        = $document->_getLineEnd();
$tab          = $document->_getTab();
$buffer       = '';

// Generate charset when using HTML5 (should happen first)
if ($document->isHtml5())
{
	$buffer .= $tab . '<meta charset="' . $document->getCharset() . '">' . $lnEnd;
}

// Generate base tag (need to happen early)
$base = $document->getBase();

if (!empty($base))
{
	$buffer .= $tab . '<base href="' . $base . '">' . $lnEnd;
}

$noFavicon = true;
$searchFor = 'image/vnd.microsoft.icon';

// @codingStandardsIgnoreStart
array_map(function($value) use(&$noFavicon, $searchFor) {
	if (isset($value['attribs']['type']) && $value['attribs']['type'] === $searchFor)
	{
		$noFavicon = false;
	}
}, array_values((array)$document->_links));
// @codingStandardsIgnoreEnd

if ($noFavicon)
{
	$client   = $app->isClient('administrator') === true ? 'administrator/' : 'site/';
	$template = $app->getTemplate(true);

	// Try to find a favicon by checking the template and root folder
	$icon = '/favicon.ico';
	$foldersToCheck = [
		JPATH_BASE,
		JPATH_ROOT . '/media/templates/' . $client . $template->template,
		JPATH_BASE . '/templates/' . $template->template,
	];

	foreach ($foldersToCheck as $base => $dir)
	{
		if ($template->parent !== ''
			&& $base === 1
			&& !is_file(JPATH_ROOT . '/media/templates/' . $client . $template->template . $icon))
		{
			$dir = JPATH_ROOT . '/media/templates/' . $client . $template->parent;
		}

		if (is_file($dir . $icon))
		{
			$urlBase = in_array($base, [0, 2]) ? Uri::base(true) : Uri::root(true);
			$base    = in_array($base, [0, 2]) ? JPATH_BASE : JPATH_ROOT;
			$path    = str_replace($base, '', $dir);
			$path    = str_replace('\\', '/', $path);
			$document->addFavicon($urlBase . $path . $icon);
			break;
		}
	}
}

// Generate META tags (needs to happen as early as possible in the head)
foreach ($document->_metaTags as $type => $tag)
{
	foreach ($tag as $name => $contents)
	{
		if ($type === 'http-equiv' && !($document->isHtml5() && $name === 'content-type'))
		{
			$buffer .= $tab . '<meta http-equiv="' . $name . '" content="'
				. htmlspecialchars($contents, ENT_COMPAT, 'UTF-8') . '">' . $lnEnd;
		}
		elseif ($type !== 'http-equiv' && !empty($contents))
		{
			$buffer .= $tab . '<meta ' . $type . '="' . $name . '" content="'
				. htmlspecialchars($contents, ENT_COMPAT, 'UTF-8') . '">' . $lnEnd;
		}
	}
}

// Don't add empty descriptions
$documentDescription = $document->getDescription();

if ($documentDescription)
{
	$buffer .= $tab . '<meta name="description" content="' . htmlspecialchars($documentDescription, ENT_COMPAT, 'UTF-8') . '">' . $lnEnd;
}

// Don't add empty generators
$generator = $document->getGenerator();

if ($generator)
{
	$buffer .= $tab . '<meta name="generator" content="' . htmlspecialchars($generator, ENT_COMPAT, 'UTF-8') . '">' . $lnEnd;
}

$buffer .= $tab . '<title>' . htmlspecialchars($document->getTitle(), ENT_COMPAT, 'UTF-8') . '</title>' . $lnEnd;

// Generate link declarations
foreach ($document->_links as $link => $linkAtrr)
{
	$buffer .= $tab . '<link href="' . $link . '" ' . $linkAtrr['relType'] . '="' . $linkAtrr['relation'] . '"';

	if (\is_array($linkAtrr['attribs']))
	{
		if ($temp = ArrayHelper::toString($linkAtrr['attribs']))
		{
			$buffer .= ' ' . $temp;
		}
	}

	$buffer .= '>' . $lnEnd;
}

echo $buffer;
