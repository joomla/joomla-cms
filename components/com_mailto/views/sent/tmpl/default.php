<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_mailto
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<div style="padding: 10px;">
	<div style="text-align:right">
		<a href="javascript: void window.close()">
			<?php echo JText::_('CLOSE_WINDOW'); ?> <img src="<?php echo JURI::base() ?>components/com_mailto/assets/close-x.png" border="0" alt="" title="" /></a>
	</div>

	<h2>
		<?php echo JText::_('EMAIL_SENT'); ?>
	</h2>
</div>
