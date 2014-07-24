<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<form action="index.php" method="post" id="adminForm">
	<fieldset>
		<legend>
			<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_COMPLETE_HEADING') ?>
		</legend>
		<p>
			<?php echo JText::sprintf('COM_JOOMLAUPDATE_VIEW_COMPLETE_MESSAGE', JVERSION); ?>
		</p>
	</fieldset>
	<?php echo JHtml::_('form.token'); ?>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="option" value="com_joomlaupdate" />
</form>
