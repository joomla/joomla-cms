<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
?>

<form action="<?php echo JRoute::_('index.php?option=com_finder&view=filter&layout=edit&filter_id=' . (int) $this->item->filter_id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-horizontal">

	<fieldset>
		<ul class="nav nav-tabs">
			<li class="active"><a href="#basic" data-toggle="tab"><?php echo JText::_('COM_FINDER_EDIT_FILTER');?></a></li>
			<li><a href="#params" data-toggle="tab"><?php echo JText::_('COM_FINDER_FILTER_FIELDSET_PARAMS');?></a></li>
			<li><a href="#details" data-toggle="tab"><?php echo JText::_('COM_FINDER_FILTER_FIELDSET_DETAILS');?></a></li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active" id="basic">
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('title'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('title'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('alias'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('alias'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('map_count'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('map_count'); ?></div>
				</div>
			</div>
			<div class="tab-pane active" id="params">
				<?php foreach($this->form->getGroup('params') as $field): ?>
					<div class="control-group">
						<?php if (!$field->hidden): ?>
							<div class="control-label"><?php echo $field->label; ?></div>
						<?php endif; ?>
						<div class="controls"><?php echo $field->input; ?></div>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="tab-pane active" id="details">
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('created_by'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('created_by'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('created_by_alias'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('created_by_alias'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('created'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('created'); ?></div>
				</div>
				<?php if ($this->item->modified_by) : ?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('modified_by'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('modified_by'); ?></div>
					</div>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('modified'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('modified'); ?></div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</fieldset>
	<div id="finder-filter-window">
		<?php echo JHtml::_('filter.slider', array('selected_nodes' => $this->filter->data)); ?>
	</div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="return" value="<?php echo JFactory::getApplication()->input->get('return', '', 'cmd');?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
