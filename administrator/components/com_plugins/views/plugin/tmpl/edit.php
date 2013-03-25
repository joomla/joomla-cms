<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
$this->fieldsets = $this->form->getFieldsets('params');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'plugin.cancel' || document.formvalidator.isValid(document.id('style-form'))) {
			Joomla.submitform(task, document.getElementById('style-form'));
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_plugins&layout=edit&extension_id=' . (int) $this->item->extension_id); ?>" method="post" name="adminForm" id="style-form" class="form-validate form-horizontal">
	<fieldset class="adminform">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#tab-basic" data-toggle="tab"><?php echo JText::_('COM_PLUGINS_BASIC_FIELDSET_LABEL');?></a></li>
			<?php foreach ($this->fieldsets as $fieldset) : ?>
			<?php if (!in_array($fieldset->name, array('description', 'basic'))) : ?>
				<?php $label = !empty($fieldset->label) ? JText::_($fieldset->label) : JText::_('COM_PLUGINS_' . $fieldset->name . '_FIELDSET_LABEL'); ?>
				<li><a href="#tab-<?php echo $fieldset->name; ?>" data-toggle="tab"><?php echo $label ?></a>
				</li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>

		<div class="tab-content">
			<div class="tab-pane active" id="tab-basic">
				<div class="row-fluid">
					<div class="span6">
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('enabled'); ?>
							</div>
							<div class="controls">
								<?php echo $this->form->getInput('enabled'); ?>
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
					</div>
					<div class="span6">
						<?php if ($this->item->xml) : ?>
						<h4>
							<?php echo ($text = (string) $this->item->xml->name) ? JText::_($text) : $this->item->element; ?>
							<br />
							<?php if ($this->item->folder) : ?>
							<span class="label"><?php echo $this->item->folder; ?></span>
							<?php endif; ?>
							<?php if ($this->item->element) : ?>
							/ <span class="label"><?php echo $this->item->element; ?></span>
							<?php endif; ?>
							<?php if ($this->item->extension_id) : ?>
							<span class="label label-info"><?php echo JText::_('JGRID_HEADING_ID');?>
								: <?php echo $this->item->extension_id; ?></span>
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
						<div class="alert alert-error"><?php echo JText::_('COM_PLUGINS_XML_ERR'); ?></div>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<?php echo $this->loadTemplate('options'); ?>
		</div>
	</fieldset>
	<input type="hidden" name="jform[extension_id]" id="jform_extension_id" value="<?php echo$this->item->extension_id; ?>" />
	<input type="hidden" name="jform[name]" id="jform_name" value="<?php echo$this->item->name; ?>" />
	<input type="hidden" name="jform[folder]" id="jform_folder" value="<?php echo$this->item->folder; ?>" />
	<input type="hidden" name="jform[element]" id="jform_element" value="<?php echo$this->item->element; ?>" />
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>