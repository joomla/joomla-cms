<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
?>

<form action="<?php echo JRoute::_('index.php?option=com_finder&view=filter&layout=edit&filter_id='.(int) $this->item->filter_id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
	<div class="width-100 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_FINDER_EDIT_FILTER'); ?></legend>
			<ul class="adminformlist">
				<li class="fltlft"><?php echo $this->form->getLabel('title'); ?><br />
				<?php echo $this->form->getInput('title'); ?></li>

				<li class="fltlft"><?php echo $this->form->getLabel('alias'); ?><br />
				<?php echo $this->form->getInput('alias'); ?></li>

				<li class="fltlft"><?php echo $this->form->getLabel('state'); ?><br />
				<?php echo $this->form->getInput('state'); ?></li>

				<li class="fltlft"><?php echo $this->form->getLabel('map_count'); ?><br />
				<?php echo $this->form->getInput('map_count'); ?></li>
			</ul>
		</fieldset>
	</div>

	<div class="clr"></div>

	<div id="finder-filter-window">
		<?php echo JHtml::_('filter.slider', array('selected_nodes' => $this->filter->data)); ?>
	</div>

	<div class="width-45 fltlft">
		<?php echo JHtml::_('sliders.start', 'param-sliders-'.$this->item->filter_id, array('useCookie'=>1)); ?>
			<?php echo JHtml::_('sliders.panel', JText::_('COM_FINDER_FILTER_FIELDSET_PARAMS'), 'filter-params'); ?>
			<fieldset class="panelform">
				<ul class="adminformlist">
					<?php foreach($this->form->getGroup('params') as $field): ?>
					<li>
						<?php if (!$field->hidden): ?>
						<?php echo $field->label; ?>
						<?php endif; ?>
						<?php echo $field->input; ?>
					</li>
					<?php endforeach; ?>
				</ul>
			</fieldset>

		<?php echo JHtml::_('sliders.end'); ?>
	</div>

	<div class="width-45 fltrt">
		<?php echo JHtml::_('sliders.start', 'filter-sliders-'.$this->item->filter_id, array('useCookie'=>1)); ?>
			<?php echo JHtml::_('sliders.panel', JText::_('COM_FINDER_FILTER_FIELDSET_DETAILS'), 'filter-details'); ?>
			<?php $details = $this->form->getGroup('details'); ?>
			<fieldset class="panelform">
				<ul class="adminformlist">
					<li><?php echo $this->form->getLabel('created'); ?>
					<?php echo $this->form->getInput('created'); ?></li>

					<?php if ($this->item->modified_by) : ?>
					<li><?php echo $this->form->getLabel('modified_by'); ?>
					<?php echo $this->form->getInput('modified_by'); ?></li>

					<li><?php echo $this->form->getLabel('modified'); ?>
					<?php echo $this->form->getInput('modified'); ?></li>
					<?php endif; ?>
				</ul>
			</fieldset>
		<?php echo JHtml::_('sliders.end'); ?>
	</div>
	<div class="clr"></div>
	<div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="return" value="<?php echo JRequest::getCmd('return');?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
