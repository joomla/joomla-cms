<?php
/**
 * @version		$Id: mod_banners.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Site
 * @subpackage	mod_syndicate
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;
?>
<a href="<?php echo $link ?>">
	<?php echo JHtml::_('image.site', 'livemarks.png', '/images/M_images/', NULL, NULL, 'feed-image'); ?> <span><?php echo $params->get('text') ?></span></a>