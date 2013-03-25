<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.combobox');
JHtml::_('formbehavior.chosen', 'select');

$hasContent = empty($this->item->module) || $this->item->module == 'custom' || $this->item->module == 'mod_custom';

$this->fieldsets = $this->form->getFieldsets('params');

$script = "Joomla.submitbutton = function(task)
	{
			if (task == 'module.cancel' || document.formvalidator.isValid(document.id('module-form'))) {";
if ($hasContent) {
	$script .= $this->form->getField('content')->save();
}
$script .= "	Joomla.submitform(task, document.getElementById('module-form'));
				if (self != top) {
					window.top.setTimeout('window.parent.SqueezeBox.close()', 1000);
				}
			} else {
				alert('" . $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')) . "');
			}
	}";

JFactory::getDocument()->addScriptDeclaration($script);
?>
<form action="<?php echo JRoute::_('index.php?option=com_modules&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="module-form" class="form-validate form-horizontal">
	<fieldset>
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#tab-basic" data-toggle="tab"><?php echo JText::_('COM_MODULES_BASIC_FIELDSET_LABEL');?></a></li>
			<?php foreach ($this->fieldsets as $fieldset) : ?>
			<?php if (!in_array($fieldset->name, array('description', 'basic'))) : ?>
				<?php $label = !empty($fieldset->label) ? JText::_($fieldset->label) : JText::_('COM_MODULES_' . $fieldset->name . '_FIELDSET_LABEL'); ?>
				<li><a href="#tab-<?php echo $fieldset->name; ?>" data-toggle="tab"><?php echo $label ?></a>
				</li>
				<?php endif; ?>
			<?php endforeach; ?>

			<?php if ($hasContent) : ?>
			<li><a href="#tab-content" data-toggle="tab"><?php echo JText::_('COM_MODULES_CUSTOM_OUTPUT');?></a></li>
			<?php endif; ?>
			<?php if ($this->item->client_id == 0) : ?>
			<li><a href="#tab-assignment" data-toggle="tab"><?php echo JText::_('COM_MODULES_MENU_ASSIGNMENT');?></a></li>
			<?php endif; ?>
		</ul>

		<div class="tab-content">
			<div class="tab-pane active" id="tab-basic">
				<div class="row-fluid">
					<div class="span6">
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('title'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('title'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('showtitle'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('showtitle'); ?>
							</div>
						</div>
						<?php if ((string) $this->item->xml->name != 'Login Form'): ?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('published'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('published'); ?>
							</div>
						</div>
						<?php endif; ?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('position'); ?>
							</div>
							<div class="controls">
								<?php echo $this->loadTemplate('positions'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('access'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('access'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('ordering'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('ordering'); ?>
							</div>
						</div>
						<?php if ((string) $this->item->xml->name != 'Login Form'): ?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('publish_up'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('publish_up'); ?>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('publish_down'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('publish_down'); ?>
							</div>
						</div>
						<?php endif; ?>

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
					</div>
					<div class="span6">
						<?php if ($this->item->xml) : ?>
						<h4>
							<?php echo ($text = (string) $this->item->xml->name) ? JText::_($text) : $this->item->module; ?>
							<br />
							<span class="label"><?php echo $this->item->client_id == 0 ? JText::_('JSITE') : JText::_('JADMINISTRATOR'); ?></span>
							/
							<span class="label"><?php echo JText::_($this->item->module); ?></span>
							<?php if ($this->item->id) : ?>
							<span class="label label-info"><?php echo JText::_('JGRID_HEADING_ID');?>
								: <?php echo $this->item->id; ?></span>
							<?php endif; ?>
						</h4>
						<?php if (isset($this->fieldsets['description'])) : ?>
							<?php if ($fields = $this->form->getFieldset('description')) : ?>
								<hr />
								<div>
									<?php foreach ($fields as $field) : ?>
										<?php if ($field->hidden) : ?>
											<?php echo $field->input; ?>
										<?php else : ?>
											<div class="control-group">
												<div class="control-label">
													<?php echo $field->label; ?>
												</div>
												<div class="controls">
													<?php echo $field->input; ?>
												</div>
											</div>
										<?php endif; ?>
									<?php endforeach; ?>
								</div>
								<?php endif; ?>
							<?php else : ?>
							<hr />
							<div>
								<?php echo JText::_(trim($this->item->xml->description)); ?>
							</div>
							<?php endif; ?>
							<?php if (isset($this->fieldsets['basic'])) : ?>
								<?php if ($fields = $this->form->getFieldset('basic')) : ?>
									<hr />
									<?php foreach ($fields as $field) : ?>
										<?php if ($field->hidden) : ?>
											<?php echo $field->input; ?>
										<?php else : ?>
											<div class="control-group">
												<div class="control-label">
													<?php echo $field->label; ?>
												</div>
												<div class="controls">
													<?php echo $field->input; ?>
												</div>
											</div>
										<?php endif; ?>
									<?php endforeach; ?>
								<?php endif; ?>
							<?php endif; ?>
						<?php else : ?>
							<div class="alert alert-error"><?php echo JText::_('COM_MODULES_ERR_XML'); ?></div>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<?php echo $this->loadTemplate('options'); ?>

			<?php if ($hasContent) : ?>
			<div class="tab-pane" id="tab-content">
				<?php echo $this->form->getInput('content'); ?>
			</div>
			<?php endif; ?>
			<?php if ($this->item->client_id == 0) : ?>
				<div class="tab-pane" id="tab-assignment">
					<?php echo $this->loadTemplate('assignment'); ?>
				</div>
			<?php endif; ?>
		</div>
	</fieldset>
	<input type="hidden" name="jform[id]" id="jform_id" value="<?php echo$this->item->id; ?>" />
	<input type="hidden" name="jform[module]" id="jform_module" value="<?php echo$this->item->module; ?>" />
	<input type="hidden" name="jform[client_id]" id="jform_client_id" value="<?php echo$this->item->client_id; ?>" />
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
