<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mailto
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<div class="p-2">
	<div class="text-right">
		<a href="javascript: void window.close()">
			<?php echo JText::_('COM_MAILTO_CLOSE_WINDOW'); ?> <?php echo JHtml::_('image', 'mailto/close-x.png', null, null, true); ?>
		</a>
	</div>
	<h2>
		<?php echo JText::_('COM_MAILTO_EMAIL_SENT'); ?>
	</h2>
</div>
