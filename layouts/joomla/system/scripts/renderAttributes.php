<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\WebAsset\WebAssetItemInterface;

/* @var $document \Joomla\CMS\Document\HtmlDocument */
/* @var $displayData [] */
/* @var $attributes [] */
extract($displayData);

$buffer                 = '';
$defaultJsMimes         = ['text/javascript', 'application/javascript', 'text/x-javascript', 'application/x-javascript'];
$html5NoValueAttributes = ['defer', 'async', 'nomodule'];

foreach ($attributes as $attrib => $value)
{
	// Don't add the 'options' attribute. This attribute is for internal use (version, conditional, etc).
	if ($attrib === 'options' || $attrib === 'src')
	{
		continue;
	}

	// Don't add type attribute if document is HTML5 and it's a default mime type. 'mime' is for B/C.
	if (\in_array($attrib, array('type', 'mime')) && $document->isHtml5() && \in_array($value, $defaultJsMimes))
	{
		continue;
	}

	// B/C: If defer and async is false or empty don't render the attribute. Also skip if value is bool:false.
	if (\in_array($attrib, array('defer', 'async')) && !$value || $value === false)
	{
		continue;
	}

	// NoValue attribute, if it have bool:true
	$isNoValueAttrib = $value === true || \in_array($attrib, $html5NoValueAttributes);

	// Don't add type attribute if document is HTML5 and it's a default mime type. 'mime' is for B/C.
	if ($attrib === 'mime')
	{
		$attrib = 'type';
	}
	// NoValue attribute in non HTML5 should contain a value, set it equal to attribute name.
	elseif ($isNoValueAttrib)
	{
		$value = $attrib;
	}

	// Add attribute to script tag output.
	$buffer .= ' ' . htmlspecialchars($attrib, ENT_COMPAT, 'UTF-8');

	if (!($document->isHtml5() && $isNoValueAttrib))
	{
		// Json encode value if it's an array.
		$value = !is_scalar($value) ? json_encode($value) : $value;

		$buffer .= '="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '"';
	}
}

echo $buffer;
