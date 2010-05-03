<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	templates.hathor
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.6
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>
<script type="text/javascript">
<!--
	function submitbutton(task)
	{
		if (task == 'weblink.cancel' || document.formvalidator.isValid(document.id('weblink-form'))) {
			submitform(task);
		}
		// @todo Deal with the editor methods
		submitform(task);
	}
// -->
</script>

<form action="<?php JRoute::_('index.php?option=com_weblinks'); ?>" method="post" name="adminForm" id="weblink-form" class="form-validate">
<div class="col main-section">
	<fieldset class="adminform">
		<legend><?php echo empty($this->item->id) ? JText::_('COM_WEBLINKS_NEW_WEBLINK') : JText::sprintf('COM_WEBLINKS_EDIT_WEBLINK', $this->item->id); ?></legend>
		<div>
			<?php echo $this->form->getLabel('title'); ?>
			<?php echo $this->form->getInput('title'); ?>
		</div>
		<div>
			<?php echo $this->form->getLabel('alias'); ?>
			<?php echo $this->form->getInput('alias'); ?>
		</div>
		<div>
			<?php echo $this->form->getLabel('url'); ?>
			<?php echo $this->form->getInput('url'); ?>
		</div>
		<div>
			<?php echo $this->form->getLabel('state'); ?>
			<?php echo $this->form->getInput('state'); ?>
		</div>
		<div>
			<?php echo $this->form->getLabel('catid'); ?>
			<?php echo $this->form->getInput('catid'); ?>
		</div>
		<div>
			<?php echo $this->form->getLabel('ordering'); ?>
			<div id="jform_ordering" class="fltlft"><?php echo $this->form->getInput('ordering'); ?></div>
		</div>
		<div>
			<?php echo $this->form->getLabel('access'); ?>
			<?php echo $this->form->getInput('access'); ?>
		</div>
		<div>
			<?php echo $this->form->getLabel('language'); ?>
			<?php echo $this->form->getInput('language'); ?>
		</div>
		<div>
			<?php echo $this->form->getLabel('id'); ?>
			<?php echo $this->form->getInput('id'); ?>
		</div>

		<div>
			<?php echo $this->form->getLabel('description'); ?>
			<div class="clr"></div>
			<?php echo $this->form->getInput('description'); ?>
		</div>
	</fieldset>
</div>

<div class="col options-section">
	<?php echo JHtml::_('sliders.start','weblink-sliders-'.$this->item->id, array('useCookie'=>1)); ?>
		<?php echo JHtml::_('sliders.panel',JText::_('JGLOBAL_FIELDSET_PUBLISHING'), 'publishing-details'); ?>
		<fieldset class="panelform">
		<legend class="element-invisible"><?php echo JText::_('JGLOBAL_FIELDSET_PUBLISHING'); ?></legend>

			<?php echo $this->form->getLabel('created_by'); ?>
			<?php echo $this->form->getInput('created_by'); ?>

			<?php echo $this->form->getLabel('created_by_alias'); ?>
			<?php echo $this->form->getInput('created_by_alias'); ?>

			<?php echo $this->form->getLabel('created'); ?>
			<?php echo $this->form->getInput('created'); ?>

			<?php echo $this->form->getLabel('publish_up'); ?>
			<?php echo $this->form->getInput('publish_up'); ?>

			<?php echo $this->form->getLabel('publish_down'); ?>
			<?php echo $this->form->getInput('publish_down'); ?>

			<?php echo $this->form->getLabel('modified'); ?>
			<?php echo $this->form->getInput('modified'); ?>

			<?php echo $this->form->getLabel('version'); ?>
			<?php echo $this->form->getInput('version'); ?>

		</fieldset>

		<?php echo $this->loadTemplate('params'); ?>

		<?php echo $this->loadTemplate('metadata'); ?>

	</div>
	<div class="clr"></div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>

