<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Valid Google language codes
$googleLanguages = array(
	'short' => array(
		'af', 'am', 'ar', 'eu', 'bn', 'bg', 'ca', 'hr', 'cs', 'da', 'nl', 'et', 'fil', 'fi',
		'fr', 'gl', 'de', 'el', 'gu', 'iw', 'hi', 'hu', 'is', 'id', 'it', 'ja', 'kn', 'ko',
		'lv', 'lt', 'ms', 'ml', 'mr', 'no', 'fa', 'pl', 'ro', 'ru', 'sr', 'sk', 'sl', 'es',
		'sw', 'sv', 'ta', 'te', 'th', 'tr', 'uk', 'ur', 'vi', 'zu'
	),
	'long' => array(
		'zh-HK', 'zh-CN', 'zh-TW', 'en-GB', 'en-US', 'fr-CA', 'pt-BR', 'pt-PT', 'es-419'
	)
);

$params = new JInput($displayData);

// Layout by default is standard but allow other 3 layouts if specified: small, medium or tall
$layout = $params->get('data-size');

if ($layout != 'small' && $layout != 'medium' && $layout != 'tall' && $layout != 'standard')
{
	$layout = 'standard';
}

// Layout by default is inline but allow other 2 layouts if specified: inline, block
$annotation = $params->get('data-annotation');

if ($annotation != 'inline' && $annotation != 'block')
{
	$annotation = 'inline';
}

// Check if the user has specified a (integer) width. Set it to the default 300 otherwise
$width = $params->get('data-width', 300, 'int');

// If not set by the user use the current URL
$href = $params->get('data-href', JUri::current());

// Check if the user has specified a (boolean) to show the number of times the page has been +1'd. Set it to the default true otherwise
$count = $params->get('data-count', true, 'bool');

// Auto-detect language - but let it be overridden if wanted from extensions languages - Should be in the form of xx_XX.
$langCode = $params->get('language', JFactory::getLanguage()->getLocale()['2']);
$language = '';

// Check the short language code based on the site's language
if (in_array(substr($langCode, 0, 2), $googleLanguages['short']))
{
	$language = 'window.___gcfg = {lang: "' . substr($langCode, 0, 2) . '"};';
}

// Check the long language code based on the site's language
elseif (in_array($langCode, $googleLanguages['long']))
{
	$language = 'window.___gcfg = {lang: "' . $langCode . '"};';
}


$document = JFactory::getDocument();
$document->addScript('https://apis.google.com/js/plusone.js');
$document->addScriptDeclaration($language);

?>
<div class="GoogleButton">
	<!-- Place this tag where you want the +1 button to render. -->
	<div class="g-plusone"
	     data-annotation="<?php echo $annotation; ?>"
	     data-width="<?php echo $width; ?>"
	     data-size="<?php echo $layout; ?>"
	     data-href="<?php echo $href; ?>"
	     data-count="<?php echo $count; ?>"
		>
	</div>
</div>