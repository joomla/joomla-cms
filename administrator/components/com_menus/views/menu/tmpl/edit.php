<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.core');
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.tabstate');

JText::script('ERROR');
?>
<form action="<?php echo JRoute::_('index.php?option=com_menus&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

	<div>
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_MENUS_MENU_DETAILS')); ?>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('menutype'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('menutype'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('description'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('description'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('client_id'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('client_id'); ?>
				</div>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php if ($this->canDo->get('core.admin')) : ?>
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'permissions', JText::_('COM_MENUS_FIELDSET_RULES')); ?>
					<?php echo $this->form->getInput('rules'); ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php endif; ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
		<input type="hidden" name="task" value="">
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
