<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

JHtml::_('behavior.framework');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.modal');
JHtml::_('formbehavior.chosen', 'select');

JText::script('ERROR');
JText::script('JGLOBAL_VALIDATION_FORM_FAILED');

$app = JFactory::getApplication();
$assoc = isset($app->item_associations) ? $app->item_associations : 0;

?>

<script type="text/javascript">
	Joomla.submitbutton = function(task, type)
	{
		if (task == 'item.setType' || task == 'item.setMenuType')
		{
			if (task == 'item.setType')
			{
				document.id('item-form').elements['jform[type]'].value = type;
				document.id('fieldtype').value = 'type';
			} else {
				document.id('item-form').elements['jform[menutype]'].value = type;
			}
			Joomla.submitform('item.setType', document.id('item-form'));
		} else if (task == 'item.cancel' || document.formvalidator.isValid(document.id('item-form')))
		{
			Joomla.submitform(task, document.id('item-form'));
		}
		else
		{
			// special case for modal popups validation response
			$$('#item-form .modal-value.invalid').each(function(field){
				var idReversed = field.id.split("").reverse().join("");
				var separatorLocation = idReversed.indexOf('_');
				var name = idReversed.substr(separatorLocation).split("").reverse().join("")+'name';
				document.id(name).addClass('invalid');
			});

			$('system-message').getElement('h4').innerHTML  = Joomla.JText._('ERROR');
			$('system-message').getElement('div').innerHTML = Joomla.JText._('JGLOBAL_VALIDATION_FORM_FAILED');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_menus&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate form-horizontal">

	<fieldset>
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_MENUS_ITEM_DETAILS', true)); ?>
				<div class="row-fluid">
					<div class="span6">
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('type'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('type'); ?>
							</div>
						</div>
						<?php if ($this->item->type == 'url') : ?>
							<?php $this->form->setFieldAttribute('link', 'readonly', 'false');?>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('link'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('link'); ?>
								</div>
							</div>
						<?php endif; ?>

						<?php if ($this->item->link == 'index.php?Itemid=') : ?>
							<?php $fieldSets = $this->form->getFieldsets('params'); ?>
							<?php foreach ($this->form->getFieldset('aliasoptions') as $field) : ?>
								<div class="control-group">
									<div class="control-label">
										<?php echo $field->label; ?>
									</div>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>

						<?php if ($this->item->link == 'index.php?option=com_wrapper&view=wrapper') : ?>
							<?php $fieldSets = $this->form->getFieldsets('params'); ?>
							<?php foreach ($this->form->getFieldset('request') as $field) : ?>
								<div class="control-group">
									<div class="control-label">
										<?php echo $field->label; ?>
									</div>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>

						<?php
							$fieldSets = $this->form->getFieldsets('request');

							if (!empty($fieldSets)) :
								$fieldSet = array_shift($fieldSets);
								$label = !empty($fieldSet->label) ? $fieldSet->label : 'COM_MENUS_' . $fieldSet->name . '_FIELDSET_LABEL';
								if (isset($fieldSet->description) && trim($fieldSet->description)) :
									echo '<p class="tip">' . $this->escape(JText::_($fieldSet->description)) . '</p>';
								endif;
							?>
								<?php $hidden_fields = ''; ?>
								<?php foreach ($this->form->getFieldset('request') as $field) : ?>
									<?php if (!$field->hidden) : ?>
									<div class="control-group">
										<div class="control-label">
											<?php echo $field->label; ?>
										</div>
										<div class="controls">
											<?php echo $field->input; ?>
										</div>
									</div>
									<?php else : $hidden_fields .= $field->input; ?>
									<?php endif; ?>
								<?php endforeach; ?>
							<?php echo $hidden_fields; ?>
						<?php endif; ?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('title'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('title'); ?>
							</div>
						</div>
						<?php if ($this->item->type == 'alias') : ?>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('aliastip'); ?>
								</div>
							</div>
						<?php endif; ?>
						<?php if ($this->item->type != 'url') : ?>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('alias'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('alias'); ?>
								</div>
							</div>
						<?php endif; ?>
						<hr />
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('published'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('published'); ?>
							</div>
						</div>
						<?php if ($this->item->type !== 'url') : ?>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('link'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('link'); ?>
								</div>
							</div>
						<?php endif ?>
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
								<?php echo $this->form->getLabel('parent_id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('parent_id'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('menuordering'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('menuordering'); ?>
							</div>
						</div>
					</div>
					<div class="span6">
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('access'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('access'); ?>
							</div>
						</div>
						<?php if ($this->item->type == 'component') : ?>
							<div class="control-group">
								<div class="control-label">
									<?php echo $this->form->getLabel('home'); ?>
								</div>
								<div class="controls">
									<?php echo $this->form->getInput('home'); ?>
								</div>
							</div>
						<?php endif; ?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('browserNav'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('browserNav'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('template_style_id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('template_style_id'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('language'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('language'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('note'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('note'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('id'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('id'); ?>
							</div>
						</div>
					</div>
				</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'options', JText::_('COM_MENUS_ADVANCED_FIELDSET_LABEL', true)); ?>
				<?php echo $this->loadTemplate('options'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php if ($assoc) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'associations', JText::_('JGLOBAL_FIELDSET_ASSOCIATIONS', true)); ?>
				<?php echo $this->loadTemplate('associations'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php endif; ?>

			<?php if (!empty($this->modules)) : ?>
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'modules', JText::_('COM_MENUS_ITEM_MODULE_ASSIGNMENT', true)); ?>
					<?php echo $this->loadTemplate('modules'); ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php endif; ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	</fieldset>

	<input type="hidden" name="task" value="" />
	<?php echo $this->form->getInput('component_id'); ?>
	<?php echo JHtml::_('form.token'); ?>
	<input type="hidden" id="fieldtype" name="fieldtype" value="" />
</form>
