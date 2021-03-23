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

$tab          = $document->_getTab();
$buffer       = '';
$wam          = $document->getWebAssetManager();
$assets       = $wam->getAssets('style', true);

// Get a list of inline assets and their relation with regular assets
$inlineAssets   = $wam->filterOutInlineAssets($assets);
$inlineRelation = $wam->getInlineRelation($inlineAssets);

// Merge with existing styleSheets, for rendering
$assets = array_merge(array_values($assets), $document->_styleSheets);

// Generate stylesheet links
foreach ($assets as $key => $item)
{
	$asset = $item instanceof WebAssetItemInterface ? $item : null;

	// Add href attribute for non Asset item
	if (!$asset)
	{
		$item['href'] = $key;
	}

	// Check for inline content "before"
	if ($asset && !empty($inlineRelation[$asset->getName()]['before']))
	{
		foreach ($inlineRelation[$asset->getName()]['before'] as $itemBefore)
		{
			$buffer .= LayoutHelper::render(
				'joomla.system.styles.renderInlineElement',
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
		'joomla.system.styles.renderElement',
		[
			'document' => $document,
			'item'     => $item
		]
	);

	// Check for inline content "after"
	if ($asset && !empty($inlineRelation[$asset->getName()]['after']))
	{
		foreach ($inlineRelation[$asset->getName()]['after'] as $itemBefore)
		{
			$buffer .= LayoutHelper::render(
				'joomla.system.styles.renderInlineElement',
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
		'joomla.system.styles.renderInlineElement',
		[
			'document' => $document,
			'item'     => $item
		]
	);
}

// Generate stylesheet declarations
foreach ($document->_style as $type => $contents)
{
	// Test for B.C. in case someone still store stylesheet declarations as single string
	if (\is_string($contents))
	{
		$contents = [$contents];
	}

	foreach ($contents as $content)
	{
		$buffer .= LayoutHelper::render(
			'joomla.system.styles.renderInlineElement',
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

echo $buffer;
