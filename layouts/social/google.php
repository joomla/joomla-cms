<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Layout by default is standard but allow other 3 layouts if specified
$layout = 'standard';

if (isset($displayData['data-size']) &&
		($displayData['data-size'] == 'small' || $displayData['data-size'] == 'medium' || $displayData['data-size'] == 'tall')
	)
{
	$layout = $displayData['data-size'];
}

// Layout by default is inline but allow other 2 layouts if specified
$annotation = 'inline';

if (isset($displayData['data-annotation']) &&
		($displayData['data-annotation'] == 'inline' || $displayData['data-annotation'] == 'none')
	)
{
	$annotation = $displayData['data-annotation'];
}

// Check if the user has specified a (integer) width. Set it to the default 300 otherwise
$width = '300';

if (isset($displayData['data-width']) && ((int) $displayData['data-width'] == $displayData['data-width']))
{
	$width = $displayData['data-width'];
}

// If not set by the user use the current URL
if (!isset($displayData['data-href']))
{
	$displayData['data-href'] = JUri::current();
}

// Check if the user has specified a (boolean) to show the number of times the page has been +1'd. Set it to the default true otherwise
$count = true;

if (isset($displayData['show-count']) && (!is_bool($displayData['show-count'])))
{
	$count = $displayData['show-count'];
}

$GlanguageShort = array(
				'af', 'am', 'ar', 'eu', 'bn', 'bg', 'ca', 'hr', 'cs', 'da', 'nl', 'et', 'fil', 'fi',
				'fr', 'gl', 'de', 'el', 'gu', 'iw', 'hi', 'hu', 'is', 'id', 'it', 'ja', 'kn', 'ko',
				'lv', 'lt', 'ms', 'ml', 'mr', 'no', 'fa', 'pl', 'ro', 'ru', 'sr', 'sk', 'sl', 'es',
				'sw', 'sv', 'ta', 'te', 'th', 'tr', 'uk', 'ur', 'vi', 'zu');
$GlanguageLong	= array('zh-HK', 'zh-CN', 'zh-TW', 'en-GB', 'en-US', 'fr-CA', 'pt-BR', 'pt-PT', 'es-419');

/**
 * Auto-detect language - but let that be overridden if wanted from extensions languages
 * Should be in the form of xx_XX.
**/
$language = JFactory::getLanguage()->getLocale()['2'];
if (isset($displayData['language']))
{
	$language = $displayData['language'];
}

// Check the short language code based on the site's language
if (in_array(substr($language, 0, 2), $GlanguageShort))
{
	$Glang = 'window.___gcfg = {lang: "' . substr($language, 0, 2) . '"};';
}

// Check the long language code based on the site's language
elseif (in_array($language, $GlanguageLong))
{
	$Glang = 'window.___gcfg = {lang: "' . $language . '"};';
}

// None of the above are matched, define no language
else
{
	$Glang = '';
}

// Get Document to add in google script if not already included
$document = JFactory::getDocument();

if (!in_array('<script type="text/javascript" src="https://apis.google.com/js/plusone.js">' . $Glang . '</script>', $document->_custom))
{
	$document->addCustomTag('<script type="text/javascript" src="https://apis.google.com/js/plusone.js">' . $Glang . '</script>');
}

?>
<div class="GoogleButton">
	<!-- Place this tag where you want the +1 button to render. -->
	<div class="g-plusone"
		data-annotation="<?php echo $annotation; ?>"
		data-width="<?php echo $width; ?>"
		data-size="<?php echo $layout; ?>"
		data-href="<?php echo $displayData['data-href']; ?>"
		data-count="<?php echo $count; ?>"
	>
	</div>
</div>