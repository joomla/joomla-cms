<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$options = array();

$option = new stdClass;
$option->value = 1;
$option->text  = JText::_('COM_JTESTREPORT_YES_SUCCESS');

$options[] = $option;

$option = new stdClass;
$option->value = 0;
$option->text  = JText::_('COM_JTESTREPORT_NO');

$options[] = $option;

$option = new stdClass;
$option->value = -1;
$option->text  = JText::_('COM_JTESTREPORT_YES_FAILED');

$options[] = $option;

$displayData['options'] = $options;
$displayData['class'] = 'btn-group';

$input = JFactory::getApplication()->input;

?>
<form action="<?php echo JRoute::_('index.php?option=com_jtestreport&view=default'); ?>" method="post" name="adminForm" id="adminForm">

	<h1><?php echo JText::_('COM_JTESTREPORT_REPORT');?></h1>


	<h2><?php echo JText::_('COM_JTESTREPORT_SITE_PERSONAL_INFO');?></h2>

	<?php foreach ($this->form->getFieldsets() as $fieldset) : ?>

		<?php $fieldsInFieldset = $this->form->getFieldset($fieldset->name); ?>

		<?php foreach ($fieldsInFieldset as $field) : ?>
			<div class="control-group">
				<?php echo $field->label;?>
				<div class="controls">
					<?php echo $field->input;?>
				</div>
			</div>
		<?php endforeach;?>
	<?php endforeach;?>

	<?php if(count($this->enabledExtensions) != 0) :?>

		<h2><?php echo JText::_('COM_JTESTREPORT_ENABLED_EXTENSIONS');?></h2>

		<table class="table table-striped">
			<thead>
				<tr>
					<th>
						<?php echo JText::_('COM_JTESTREPORT_NAME');?>
					</th>
					<th>
						<?php echo JText::_('COM_JTESTREPORT_TYPE');?>
					</th>
					<th>
						<?php echo JText::_('COM_JTESTREPORT_VERSION');?>
					</th>
					<th>
						<?php echo JText::_('COM_JTESTREPORT_TESTED');?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($this->enabledExtensions as $item) : ?>

				<tr>
					<td>
						<?php echo JText::_($item->name);?>
					</td>
					<td>
						<?php echo $item->type;?>
					</td>
					<td>
						<?php echo $item->version;?>
					</td>
					<td>
						<?php $id = 'eid' . $item->extension_id;?>

						<?php $displayData['id'] = 'jform_' . $id;?>
						<?php $displayData['value'] = $input->get($id, 0);?>
						<?php $displayData['name'] = 'jform[' . $id . ']';?>
						<div class="control-group">
							<div class="controls">
								<?php echo JLayoutHelper::render('joomla.form.field.radio', $displayData);?>
							</div>
						</div>
					</td>
				</tr>

				<?php endforeach;?>
			</tbody>
		</table>

	<?php endif; ?>

	<?php if(count($this->unenabledExtensions) != 0) :?>

		<h2><?php echo JText::_('COM_JTESTREPORT_UNENABLED_EXTENSIONS');?></h2>
		<table class="table table-striped">
			<thead>
			<tr>
				<th>
					<?php echo JText::_('COM_JTESTREPORT_NAME');?>
				</th>
				<th>
					<?php echo JText::_('COM_JTESTREPORT_TYPE');?>
				</th>
				<th>
					<?php echo JText::_('COM_JTESTREPORT_VERSION');?>
				</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($this->unenabledExtensions as $item) : ?>
				<tr>
					<td>
						<?php echo $item->name;?>
					</td>
					<td>
						<?php echo $item->type;?>
					</td>
					<td>
						<?php echo $item->version;?>
					</td>
				</tr>
			<?php endforeach;?>
			</tbody>
		</table>
	<?php endif; ?>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
