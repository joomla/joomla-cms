<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.combobox');
$hasContent = empty($this->item->module) || $this->item->module == 'custom' || $this->item->module == 'mod_custom';

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
				alert('".$this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'))."');
			}
	}";

JFactory::getDocument()->addScriptDeclaration($script);
?>
<form action="<?php echo JRoute::_('index.php?option=com_modules&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="module-form" class="form-validate form-horizontal">
	<fieldset>
		<ul class="nav nav-tabs">
		  <li class="active"><a href="#details" data-toggle="tab"><?php echo JText::_('JDETAILS');?></a></li>
		  <li><a href="#options" data-toggle="tab"><?php echo JText::_('COM_MODULES_BASIC_FIELDSET_LABEL');?></a></li>
		  <?php if ($hasContent) : ?>
		  	<li><a href="#custom" data-toggle="tab"><?php echo JText::_('COM_MODULES_CUSTOM_OUTPUT');?></a></li>
		  <?php endif; ?>
		  <?php if ($this->item->client_id == 0) :?>
			  <li><a href="#assignment" data-toggle="tab"><?php echo JText::_('COM_MODULES_MENU_ASSIGNMENT');?></a></li>
		  <?php endif; ?>
		</ul>
		
		<div class="tab-content">
		  <div class="tab-pane active" id="details">
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
	  		<div class="control-group">
	  			<div class="control-label">
	  				<?php echo $this->form->getLabel('position'); ?>
	  				</div>
	  				<div class="controls">
	  					<?php echo $this->form->getInput('position'); ?>
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
  			<?php if ($this->item->id) : ?>
	  			<div class="control-group">
	  				<div class="control-label">
	  					<?php echo $this->form->getLabel('id'); ?>
	  				</div>
	  				<div class="controls">
	  					<?php echo $this->form->getInput('id'); ?>
	  				</div>
	  			</div>
  			<?php endif; ?>
	  	
	  		<div class="control-group">
	  			<div class="control-label">
	  				<?php echo $this->form->getLabel('module'); ?>
	  			</div>
	  			<div class="controls">
		  			<?php echo $this->form->getInput('module'); ?>
		  			<input type="text" size="35" value="<?php if ($this->item->xml) echo ($text = (string) $this->item->xml->name) ? JText::_($text) : $this->item->module;else echo JText::_('COM_MODULES_ERR_XML');?>" class="readonly" readonly="readonly" />
	  			</div>
	  		</div>
	  		<div class="control-group">
	  			<div class="control-label">
	  				<?php echo $this->form->getLabel('client_id'); ?>
	  			</div>
	  			<div class="controls">
	  				<input type="text" size="35" value="<?php echo $this->item->client_id == 0 ? JText::_('JSITE') : JText::_('JADMINISTRATOR'); ?>	" class="readonly" readonly="readonly" />
	  				<?php echo $this->form->getInput('client_id'); ?>
	  			</div>
			</div>
  			<?php if ($this->item->xml) : ?>
  				<?php if ($text = trim($this->item->xml->description)) : ?>
  				<div class="control-group">
  					<label class="control-label">
  						<?php echo JText::_('COM_MODULES_MODULE_DESCRIPTION'); ?>
  					</label>
  					<div class="controls disabled">
  						<?php echo JText::_($text); ?>
  					</div>
  				</div>
  				<?php endif; ?>
  			<?php else : ?>
  				<div class="alert alert-error"><?php echo JText::_('COM_MODULES_ERR_XML'); ?></div>
  			<?php endif; ?>
		  </div>
		  <div class="tab-pane" id="options">
		  	<?php echo $this->loadTemplate('options'); ?>
		  </div>
		  <?php if ($hasContent) : ?>
		  	<div class="tab-pane" id="custom">
		  		<?php echo $this->form->getInput('content'); ?>
		  	</div>
		  <?php endif; ?>
		  <?php if ($this->item->client_id == 0) :?>
			  <div class="tab-pane" id="assignment">
			  	<?php echo $this->loadTemplate('assignment'); ?>
			  </div>
		  <?php endif; ?>
		</div>
		  
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</fieldset>
</form>
