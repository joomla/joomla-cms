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

$params = new JInput($displayData);

$appId = $params->get('appid', '', 'int');

if ($appId != '')
{
	$appId = '&appId=' . $appId;
}

// If not set by the user use the current URL
$href = $params->get('data-href', JUri::current());

// Check if the user has specified a (integer) width. Set it to the default 450 otherwise
$width = $params->get('data-width', 450, 'int');

// Check if the user has specified a (boolean) to show faces. Set it to the default true otherwise
$showFaces = $params->get('show-faces', true, 'bool');

// Layout by default is standard but allow other 2 layouts if specified: button_count, box_count
$layout = $params->get('data-layout');

if ($layout != 'button_count' && $layout != 'box_count' && $layout != 'standard')
{
	$layout = 'standard';
}

// Default action is like but allow recommend if specified
$like = $params->get('data-action');

if ($like != 'recommend' && $like != 'like')
{
	$action = 'like';
}

// Set the default colour scheme as light unless dark is specified
$colour = $params->get('data-colorscheme');

if ($colour != 'light' && $colour != 'dark')
{
	$colour = 'light';
}

// Get Document to add in FB script if not already included
$document = JFactory::getDocument();

/**
 * Auto-detect language - but let that be overridden if wanted from extensions languages
 * Should be in the form of xx_XX.
**/
$language = $params->get('language', JFactory::getLanguage()->getLocale()['2']);

if (!in_array('<script src="http://connect.facebook.net/' . $language . '/all.js#xfbml=1' . $appId . '&status=0"></script>', $document->_custom))
{
	$document->addCustomTag('<script src="http://connect.facebook.net/' . $language . '/all.js#xfbml=1' . $appId . '&status=0"></script>');
}
?>
<!-- Facebook button JLayout -->
<div class="FacebookButton">
	<div class="fb-like"
		data-href="<?php echo $href; ?>"
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