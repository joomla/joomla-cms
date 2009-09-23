<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_menus
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
jimport('joomla.html.pane');
$pane = &JPane::getInstance('sliders', array('allowAllClose' => true));

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>

<script type="text/javascript">
<!--
	function submitbutton(task)
	{
		if (task == 'item.cancel' || document.formvalidator.isValid($('item-form'))) {
			submitform(task);
		}
	}
// -->
</script>

<form action="<?php JRoute::_('index.php?option=com_menus'); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

<div class="width-50 fltlft">
	<fieldset>
		<legend><?php echo JText::_('Menus_Item_Details');?></legend>

			<?php echo $this->form->getLabel('title'); ?>
			<?php echo $this->form->getInput('title'); ?>

			<?php echo $this->form->getLabel('alias'); ?>
			<?php echo $this->form->getInput('alias'); ?>
				
			<?php echo $this->form->getLabel('type'); ?>
			<?php echo $this->form->getInput('type'); ?>
				
			<?php if ($this->item->type =='url'){ ?>
				<?php echo $this->form->getLabel('link'); ?>
				<?php echo $this->form->getInput('link'); ?>
			<?php } ?>		
				
			<ul id="overviewlist">
				<li id="jform_menutype_label"><?php echo JText::_('Menus_Item_Menutype');?></li>
				<li id="jform_menutype"><?php echo $this->form->getValue('menutype'); ?></li>

				<li id="jform_parentid_label"><?php echo JText::_('Menus_Item_ParentID');?></li>
				<li id="jform_parentid"><?php echo $this->form->getValue('parent_id'); ?></li>
					
				<li id="jform_published_label"><?php echo JText::_('Menus_Item_Published');?></li>
				<li id="jform_published"><?php echo $this->form->getValue('published'); ?></li>
				
				<li id="jform_access_label"><?php echo JText::_('Menus_Item_Access');?></li>
				<li id="jform_access"><?php echo $this->form->getValue('access'); ?></li>
			</ul>
			<?php echo $this->loadTemplate('required'); ?>
	</fieldset>
</div>

<div class="width-50 fltrt">
<?php echo $pane->startPane('menu-pane'); ?>
	<?php // Get Advanced Menu params
	 echo $pane->startPanel(JText::_('Menu_Advanced_Menu_Options'), 'advanced-options'); ?>			
			<fieldset>
				<?php echo $this->loadTemplate('advanced'); ?>
			</fieldset>		
			<?php echo $pane->endPanel(); ?>
		<div class="clr"></div>
	
	
	<?php //get the menu parameters that are automatically set but may be modified.
		echo $this->loadTemplate('options'); ?>
	
	<div class="clr"></div>

	<?php //sliders for module selection						
		 echo $pane->startPanel(JText::_('Menu_Item_Module_Assignment'), 'module-options'); ?>			
			<fieldset>
				<?php echo $this->loadTemplate('modules'); ?>
			</fieldset>		
			<?php echo $pane->endPanel(); ?>

	<?php echo $pane->endPane(); ?>	
</div>	
	<input type="hidden" name="task" value="" />
	<?php echo $this->form->getInput('component_id'); ?>
	<?php echo JHtml::_('form.token'); ?>
</form>
<div class="clr"></div>
