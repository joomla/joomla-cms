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
$pane = &JPane::getInstance('sliders');

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHTML::_('behavior.modal');
?>

<script type="text/javascript">
<!--
	function submitbutton(task)
	{
		if (task == 'item.cancel' || document.formvalidator.isValid(document.id('item-form'))) {
			submitform(task);
		}
	}
// -->
</script>

<form action="<?php JRoute::_('index.php?option=com_menus'); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

<div class="width-60 fltlft">
	<fieldset class="adminform">
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

			<?php if ($this->item->type !=='url'){ ?>
				<?php echo $this->form->getLabel('link'); ?>
				<?php echo $this->form->getInput('link'); ?>
			<?php } ?>

			<?php echo $this->form->getLabel('published'); ?>
			<?php echo $this->form->getInput('published'); ?>

			<?php echo $this->form->getLabel('access'); ?>
			<?php echo $this->form->getInput('access'); ?>

			<?php echo $this->form->getLabel('menutype'); ?>
			<?php echo $this->form->getInput('menutype'); ?>

			<?php echo $this->form->getLabel('parent_id'); ?>
			<?php echo $this->form->getInput('parent_id'); ?>

			<?php echo $this->form->getLabel('browserNav'); ?>
			<?php echo $this->form->getInput('browserNav'); ?>

			<?php echo $this->form->getLabel('home'); ?>
			<?php echo $this->form->getInput('home'); ?>

			<?php echo $this->form->getLabel('template_style_id'); ?>
			<?php echo $this->form->getInput('template_style_id'); ?>
	</fieldset>
</div>

<div class="width-40 fltrt">
<?php echo $pane->startPane('menu-pane'); ?>

	<?php //get the menu parameters that are automatically set but may be modified.
		echo $this->loadTemplate('options'); ?>

	<div class="clr"></div>

	<?php if (!empty($this->modules)) : ?>
		<?php echo $pane->startPanel(JText::_('Menu_Item_Module_Assignment'), 'module-options'); ?>
		<fieldset>
			<?php echo $this->loadTemplate('modules'); ?>
		</fieldset>
		<?php echo $pane->endPanel(); ?>
	<?php endif; ?>

	<?php echo $pane->endPane(); ?>
</div>
	<input type="hidden" name="task" value="" />
	<?php echo $this->form->getInput('component_id'); ?>
	<?php echo JHtml::_('form.token'); ?>
</form>
<div class="clr"></div>

