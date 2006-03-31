<?php
/**
* @version $Id: mod_rssfeed.php 588 2005-10-23 15:20:09Z stingrey $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

//check if cache diretory is writable as cache files will be created for the feed
$cacheDir = $mosConfig_cachepath.'/';
if (!is_writable($cacheDir))
{
	echo '<div>';
	echo JText::_('Please make cache directory writable.');
	echo '</div>';

	return;
}

$option	= JRequest::getVar( 'option', '', 'get' );
$task		= JRequest::getVar( 'task', '', 'get' );

// check for specific core component disablement
switch ($option)
{
	case 'com_rss' :
	case 'com_newsfeeds' :
	case 'com_search' :
	case 'com_wrapper' :
	case 'com_login' :
	case 'com_poll' :
		// do not display for specific core components    
		echo '&nbsp;';
		return;
		break;
}

if (!defined('_JOS_SYNDICATE_MODULE'))
{
	/** ensure that functions are declared only once */
	define('_JOS_SYNDICATE_MODULE', 1);

	function outputSyndicateLink($check, $link, $img_default, $img_file, $img_alt, $img_name, $moduleclass_sfx)
	{
		if ($check) {
			$img = mosAdminMenus::ImageCheck($img_default, '/images/M_images/', $img_file, '/images/M_images/', $img_alt, $img_name);
			?>
			<div class="syndicate_link<?php echo $moduleclass_sfx;?>">
				<a href="<?php echo sefRelToAbs( $link ); ?>">
					<?php echo $img ?></a>
			</div>
			<?php

		}
	}
}

// paramters
$moduleclass_sfx	= $params->get('moduleclass_sfx', '');
$rss091				= $params->get('rss091', 1);
$rss10				= $params->get('rss10', 1);
$rss20				= $params->get('rss20', 1);
$atom				= $params->get('atom', 1);
$opml				= $params->get('opml', 1);
$rss091_image		= $params->get('rss091_image', '');
$rss10_image		= $params->get('rss10_image', '');
$rss20_image		= $params->get('rss20_image', '');
$atom_image			= $params->get('atom_image', '');
$opml_image			= $params->get('opml_image', '');

$from = @ $_SERVER['QUERY_STRING'];

if ($from) {
	$parts	= explode('option=', $from);
	$url	= ampReplace($parts[1]);

	$linkRSS091	= 'index.php?option=com_syndicate&amp;feed=RSS0.91&amp;type='.$url;
	$linkRSS10	= 'index.php?option=com_syndicate&amp;feed=RSS1.0&amp;type='.$url;
	$linkRSS20	= 'index.php?option=com_syndicate&amp;feed=RSS2.0&amp;type='.$url;
	$linkATOM03	= 'index.php?option=com_syndicate&amp;feed=ATOM0.3&amp;type='.$url;
	$linkOPML	= 'index.php?option=com_syndicate&amp;feed=OPML&amp;type='.$url;
	?>
	<div class="syndicate<?php echo $moduleclass_sfx;?>">
	<?php
	outputSyndicateLink($rss091, $linkRSS091, 'rss091.gif', $rss091_image, 'RSS 0.91', 'RSS091', $moduleclass_sfx);
	outputSyndicateLink($rss10, $linkRSS10, 'rss10.gif', $rss10_image, 'RSS 1.0', 'RSS10', $moduleclass_sfx);
	outputSyndicateLink($rss20, $linkRSS20, 'rss20.gif', $rss20_image, 'RSS 2.0', 'RSS20', $moduleclass_sfx);
	outputSyndicateLink($atom, $linkATOM03, 'atom03.gif', $atom_image, 'ATOM 0.3', 'ATOM03', $moduleclass_sfx);
	outputSyndicateLink($opml, $linkOPML, 'opml.png', $opml_image, 'OPML', 'OPML', $moduleclass_sfx);
	?>
	</div>
	<?php
} else {
	echo '&nbsp;';
}
?>