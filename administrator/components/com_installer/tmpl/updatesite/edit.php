<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

?>
<form action="<?php echo JRoute::_('index.php?option=com_installer&view=updatesite&layout=edit&update_site_id=' . (int) $this->item->update_site_id); ?>"
      method="post" name="adminForm" id="adminForm">
	<div class="form-horizontal">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_INSTALLER_UPDATESITE_EDIT_DETAILS'); ?></legend>
			<div class="row">
				<div class="col">
					<?php foreach ($this->form->getFieldset() as $field): ?>
						<div class="control-group">
							<div class="control-label"><?php echo $field->label; ?></div>
							<div class="controls"><?php echo $field->input; ?></div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</fieldset>
	</div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
