<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'plugin.cancel' || document.formvalidator.isValid(document.id('style-form'))) {
			Joomla.submitform(task, document.getElementById('style-form'));
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_plugins&layout=edit&extension_id='.(int) $this->item->extension_id); ?>" method="post" name="adminForm" id="style-form" class="form-validate form-horizontal">
	<fieldset class="adminform">
		<ul class="nav nav-tabs">
		  <li class="active"><a href="#details" data-toggle="tab"><?php echo JText::_('JDETAILS');?></a></li>
		  <li><a href="#options" data-toggle="tab"><?php echo JText::_('COM_PLUGINS_BASIC_FIELDSET_LABEL');?></a></li>
		</ul>
		
		<div class="tab-content">
			<div class="tab-pane active" id="details">
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('name'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('name'); ?>
						<span class="readonly plg-name"><?php echo JText::_($this->item->name);?></span>
					</div>
				</div>
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
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('folder'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('folder'); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo $this->form->getLabel('element'); ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getInput('element'); ?>
					</div>
				</div>
				<?php if ($this->item->extension_id) : ?>
					<div class="control-group">
						<div class="control-label">
							<?php echo $this->form->getLabel('extension_id'); ?>
						</div>
						<div class="controls">
							<?php echo $this->form->getInput('extension_id'); ?>
						</div>
					</div>
				<?php endif; ?>
				<!-- Plugin metadata -->
				<?php if ($this->item->xml) : ?>
					<?php if ($text = trim($this->item->xml->description)) : ?>
						<div class="control-group">									
							<label id="jform_extdescription-lbl" class="control-label">
								<?php echo JText::_('JGLOBAL_DESCRIPTION'); ?>
							</label>
							<div class="controls disabled">
								<?php echo JText::_($text); ?>
							</div>
						</div>
					<?php endif; ?>
				<?php else : ?>
					<div class="alert alert-error">
						<?php echo JText::_('COM_PLUGINS_XML_ERR'); ?>
					</div>
				<?php endif; ?>
			</div>
			<div class="tab-pane" id="options">
				<?php echo $this->loadTemplate('options'); ?>
			</div>
		</div>
	</fieldset>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
