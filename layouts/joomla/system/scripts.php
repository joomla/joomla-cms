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

/* @var $document \Joomla\CMS\Document\HtmlDocument */
/* @var $displayData [] */
extract($displayData);

// Get line endings
$lnEnd        = $document->_getLineEnd();
$tab          = $document->_getTab();
$wam          = $document->getWebAssetManager();
$assets       = $wam->getAssets('script', true);
$buffer       = '';
$renderedSrc  = [];

// Get a list of inline assets and their relation with regular assets
$inlineAssets   = $wam->filterOutInlineAssets($assets);
$inlineRelation = $wam->getInlineRelation($inlineAssets);

// Merge with existing scripts, for rendering
$assets = array_merge(array_values($assets), $document->_scripts);

// Generate script file links
foreach ($assets as $key => $item)
{
	// Check whether we have an Asset instance, or old array with attributes
	$asset = $item instanceof WebAssetItemInterface ? $item : null;

	// Add src attribute for non Asset item
	if (!$asset)
	{
		$item['src'] = $key;
	}

	// Check for inline content "before"
	if ($asset && !empty($inlineRelation[$asset->getName()]['before']))
	{
		foreach ($inlineRelation[$asset->getName()]['before'] as $itemBefore)
		{
			$buffer .= LayoutHelper::render(
				'joomla.system.scripts.renderInlineElement',
				[
					'document' => $document,
					'item'     => $itemBefore
				]
			);

			// Remove this item from inline queue
			unset($inlineAssets[$itemBefore->getName()]);
		}
	}

	$buffer .= LayoutHelper::render(
		'joomla.system.scripts.renderElement',
		[
			'document' => $document,
			'item'     => $item
		]
	);

	// To prevent double rendering
   // $renderedSrc[$src] = true;

	// Check for inline content "after"
	if ($asset && !empty($inlineRelation[$asset->getName()]['after']))
	{
		foreach ($inlineRelation[$asset->getName()]['after'] as $itemBefore)
		{
			$buffer .= LayoutHelper::render(
				'joomla.system.scripts.renderInlineElement',
				[
					'document' => $document,
					'item'     => $itemBefore
				]
			);

			// Remove this item from inline queue
			unset($inlineAssets[$itemBefore->getName()]);
		}
	}
}

// Generate script declarations for assets
foreach ($inlineAssets as $item)
{
	$buffer .= LayoutHelper::render(
		'joomla.system.scripts.renderInlineElement',
		[
			'document' => $document,
			'item'     => $item
		]
	);
}

// Generate script declarations for old scripts
foreach ($document->_script as $type => $contents)
{
	// Test for B.C. in case someone still store script declarations as single string
	if (\is_string($contents))
	{
		$contents = [$contents];
	}

	foreach ($contents as $content)
	{
		$buffer .= LayoutHelper::render(
			'joomla.system.scripts.renderInlineElement',
			[
				'document' => $document,
				'item'     => [
					'type'     => $type,
					'content'  => $content,
				]
			]
		);
	}
}

// Output the custom tags - array_unique makes sure that we don't output the same tags twice
foreach (array_unique($document->_custom) as $custom)
{
	$buffer .= $tab . $custom . $lnEnd;
}

echo $buffer;
