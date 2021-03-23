<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\WebAsset\WebAssetItemInterface;

/* @var $displayData [] */
/* @var $document \Joomla\CMS\Document\HtmlDocument */
/* @var $item  */
extract($displayData);

$buffer = '';
$asset  = $item instanceof WebAssetItemInterface ? $item : null;
$src    = $asset ? $asset->getUri() : ($item['src'] ?? '');

// Make sure we have a src, and it not already rendered
if (!$src || !empty($document->renderedSrc[$src]) || ($asset && $asset->getOption('webcomponent')))
{
	return '';
}

$lnEnd        = $document->_getLineEnd();
$tab          = $document->_getTab();
$mediaVersion = $document->getMediaVersion();

// Get the attributes and other options
if ($asset)
{
	$attribs     = $asset->getAttributes();
	$version     = $asset->getVersion();
	$conditional = $asset->getOption('conditional');

	// Add an asset info for debugging
	if (JDEBUG)
	{
		$attribs['data-asset-name'] = $asset->getName();

		if ($asset->getDependencies())
		{
			$attribs['data-asset-dependencies'] = implode(',', $asset->getDependencies());
		}
	}
}
else
{
	$attribs     = $item;
	$version     = isset($attribs['options']['version']) ? $attribs['options']['version'] : '';
	$conditional = !empty($attribs['options']['conditional']) ? $attribs['options']['conditional'] : null;
}

// To prevent double rendering
$document->renderedSrc[$src] = true;

// Check if script uses media version.
if ($version && strpos($src, '?') === false && ($mediaVersion || $version !== 'auto'))
{
	$src .= '?' . ($version === 'auto' ? $mediaVersion : $version);
}

$buffer .= $tab;

// This is for IE conditional statements support.
if (!\is_null($conditional))
{
	$buffer .= '<!--[if ' . $conditional . ']>';
}

// Render the element with attributes
$buffer .= '<script src="' . htmlspecialchars($src) . '"';
$buffer .= LayoutHelper::render(
	'joomla.system.scripts.renderAttributes',
	[
		'document'   => $document,
		'attributes' => $attribs
	]
);
$buffer .= '></script>';

// This is for IE conditional statements support.
if (!\is_null($conditional))
{
	$buffer .= '<![endif]-->';
}

$buffer .= $lnEnd;

echo $buffer;
