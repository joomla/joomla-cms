<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// An integer to tell facebook the appID of the fb user. Defaults to an empty string
$appId = '';

if (isset($displayData['appid']) && ((int) $displayData['appid'] == $displayData['appid']))
{
	$appId = '&appId=' . $displayData['appid'];
}

// If not set by the user use the current URL
if (!isset($displayData['data-href']))
{
	$displayData['data-href'] = JUri::current();
}

// Check if the user has specified a (integer) width. Set it to the default 450 otherwise
$width = '450';

if (isset($displayData['data-width']) && ((int) $displayData['data-width'] == $displayData['data-width']))
{
	$width = $displayData['data-width'];
}

// Check if the user has specified a (boolean) to show faces. Set it to the default true otherwise
$showFaces = true;

if (isset($displayData['show-faces']) && (!is_bool($displayData['show-faces'])))
{
	$showFaces = $displayData['show-faces'];
}

// Layout by default is standard but allow other 2 layouts if specified
$layout = 'standard';

if (isset($displayData['data-layout']) &&
		($displayData['data-layout'] == 'button_count' || $displayData['data-layout'] == 'box_count')
	)
{
	$layout = $displayData['data-layout'];
}

// Default action is like but allow recommend if specified
$action = 'like';

if (isset($displayData['data-action']) && $displayData['data-action'] == 'recommend')
{
	$action = 'recommend';
}

// Set the default colour scheme as light unless dark is specified
$colour = 'light';

if (isset($displayData['data-colorscheme']) && $displayData['data-colorscheme'] == 'dark')
{
	$colour = 'dark';
}

// Get Document to add in FB script if not already included
$document = JFactory::getDocument();

/**
 * Auto-detect language - but let that be overridden if wanted from extensions languages
 * Should be in the form of xx_XX.
**/
$language = JFactory::getLanguage()->getLocale()['2'];
if (isset($displayData['language']))
{
	$language = $displayData['language'];
}

if (!in_array('<script src="http://connect.facebook.net/' . $language . '/all.js#xfbml=1' . $appId . '"></script>', $document->_custom))
{
	$document->addCustomTag('<script src="http://connect.facebook.net/' . $language . '/all.js#xfbml=1"></script>');
}
?>
<!-- Facebook button JLayout -->
<div class="FacebookButton">
	<div class="fb-like"
		data-href="<?php echo $displayData['data-href']; ?>"
		data-send="true"
		data-layout="<?php echo $layout; ?>"
		data-show-faces="<?php echo $showFaces; ?>"
		data-action="<?php echo $action; ?>"
		data-font="arial"
		data-width="<?php echo $width; ?>"
		data-colorscheme="<?php echo $colour; ?>"
	>
	</div>
</div>