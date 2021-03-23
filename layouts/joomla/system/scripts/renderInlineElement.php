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
$lnEnd  = $document->_getLineEnd();
$tab    = $document->_getTab();

if ($item instanceof WebAssetItemInterface)
{
	$attribs = $item->getAttributes();
	$content = $item->getOption('content');
}
else
{
	$attribs = $item;
	$content = $item['content'] ?? '';

	unset($attribs['content']);
}

// Do not produce empty elements
if (!$content)
{
	echo '';
}
else
{
	// Add "nonce" attribute if exist
	if ($document->cspNonce)
	{
		$attribs['nonce'] = $document->cspNonce;
	}

	$buffer .= $tab . '<script';
	$buffer .= LayoutHelper::render(
		'joomla.system.scripts.renderAttributes',
		[
			'document'   => $document,
			'attributes' => $attribs
		]
	);

	$buffer .= '>';

	// This is for full XHTML support.
	if ($document->_mime !== 'text/html')
	{
		$buffer .= $tab . $tab . '//<![CDATA[' . $lnEnd;
	}

	$buffer .= $content;

	// See above note
	if ($document->_mime !== 'text/html')
	{
		$buffer .= $tab . $tab . '//]]>' . $lnEnd;
	}

	$buffer .= '</script>' . $lnEnd;

	echo $buffer;
}
