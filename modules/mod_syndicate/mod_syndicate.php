<?php
/**
* @version $Id: mod_rssfeed.php 588 2005-10-23 15:20:09Z stingrey $
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

$option	= JRequest::getVar( 'option', '', 'get' );
$task	= JRequest::getVar( 'task', '', 'get' );

if (!defined('_SYNDICATE_MODULE'))
{
	/** ensure that functions are declared only once */
	define('_SYNDICATE_MODULE', 1);

	function outputSyndicateLink( $link, &$params )
	{
		$img = mosAdminMenus::ImageCheck('livemarks.png', '/images/M_images/');
		?>
			<a href="<?php echo sefRelToAbs( $link ); ?>">
				<?php echo $img ?> <span><?php echo $params->get('text') ?></span>
			</a>
		<?php
	}
}

// paramters
$params->def('text', 'Feed Entries');

$link	= 'index.php?option=com_frontpage&amp;format=rss';
outputSyndicateLink( $link, $params );