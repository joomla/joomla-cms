<?php
/**
 * @version		$Id: edit.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla.Administrator
 * @subpackage	com_categories
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
?>

<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task == 'category.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task, document.getElementById('item-form'));
		} else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<ul id="submenu" class="out">
	<li class="item-content"><a href="#" class="active"><?php echo JText::_('TPL_MINIMA_CONTENT_LABEL_CONTENT'); ?></a></li>
	<li class="item-parameters"><a href="#"><?php echo JText::_('TPL_MINIMA_CONTENT_LABEL_PARAMETERS'); ?></a></li>
	<?php if ($this->canDo->get('core.admin')): ?>
	<li class="item-permissions"><a href="#"><?php echo JText::_('JGLOBAL_ACTION_PERMISSIONS_LABEL'); ?></a></li>
	<?php endif; ?>
</ul>
<form action="<?php echo JRoute::_('index.php?option=com_categories&extension='.JRequest::getCmd('extension', 'com_content').'&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="item-form" class="form-validate">
	<div id="item-basic">
	<div class="width-70 fltlft">
		<fieldset class="adminform">
			<legend><?php echo empty($this->item->id) ? JText::_('TPL_MINIMA_CATEGORY_NEW_CATEGORY') : JText::sprintf('TPL_MINIMA_CATEGORY_EDIT_CATEGORY', $this->item->id); ?></legend>
			<ol class="adminformlist">
				<li class="item-title">
					<?php echo $this->form->getLabel('title'); ?>
					<?php echo $this->form->getInput('title'); ?>
				</li>
				<li class="item-text">
					<?php echo $this->form->getLabel('description'); ?>			
					<?php echo $this->form->getInput('description'); ?>
				</li>
			</ol>			
		</fieldset>
	</div>	

	<div class="width-30 fltrt item-info">
		<fieldset class="adminform">
		<legend><?php echo JText::_('TPL_MINIMA_CATEGORY_LABEL_INFORMATION'); ?></legend>
		<ol class="adminformlist">	
				<li><?php echo $this->form->getLabel('alias'); ?>
				<?php echo $this->form->getInput('alias'); ?></li>

				<li><?php echo $this->form->getLabel('extension'); ?>
				<?php echo $this->form->getInput('extension'); ?></li>

				<li><?php echo $this->form->getLabel('parent_id'); ?>
				<?php echo $this->form->getInput('parent_id'); ?></li>

				<li><?php echo $this->form->getLabel('published'); ?>
				<?php echo $this->form->getInput('published'); ?></li>

				<li><?php echo $this->form->getLabel('access'); ?>
				<?php echo $this->form->getInput('access'); ?></li>			
				
				<li><?php echo $this->form->getLabel('language'); ?>
				<?php echo $this->form->getInput('language'); ?></li>
	
				<li><?php echo $this->form->getLabel('id'); ?>
				<?php echo $this->form->getInput('id'); ?></li>
		</ol>
		</fieldset>		
	</div>	
	</div><!-- #item-basic -->	
	
	<div id="item-advanced">
	    <ul class="vertical-tabs">			
		    <li class="basic-options"><a href="#" class="active"><?php echo JText::_('JDETAILS'); ?></a></li>
			<li class="metadata"><a href="#"><?php echo JText::_('JGLOBAL_FIELDSET_METADATA_OPTIONS'); ?></a></li>
		</ul>
		<div id="tabs">			
			<fieldset id="basic-options" class="panelform">
				<?php echo $this->loadTemplate('options'); ?>						
			</fieldset>
			<fieldset id="metadata" class="panelform">
				<?php echo $this->loadTemplate('metadata'); ?>			
			</fieldset>
		</div><!-- /#tabs -->
	</div><!-- /#item-advanced -->
	
	<div id="item-permissions">
	<?php if ($this->canDo->get('core.admin')): ?>
		<div  class="width-100 fltlft">			
			<fieldset class="panelform">
				<?php echo $this->form->getLabel('rules'); ?>
				<?php echo $this->form->getInput('rules'); ?>
			</fieldset>
		</div>
	<?php endif; ?>	    
	</div><!-- /#item-permissions -->
    
	
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	
</form>
