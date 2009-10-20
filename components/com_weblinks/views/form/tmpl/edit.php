<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>

<script language="javascript" type="text/javascript">
function submitbutton(task) {
	if (task == 'weblink.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
		<?php //echo $this->form->fields['introtext']->editor->save('jform[introtext]'); ?>
		submitform(task);
	}
}
</script>

<?php if ($this->params->get('show_page_title', 1)) : ?>
<h2 class="<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
	<?php echo $this->escape($this->params->get('page_title')); ?>
</h2>
<?php endif; ?>

<form action="<?php echo JRoute::_('index.php?option=com_weblinks'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<fieldset>
		<legend><?php echo JText::_('Weblink'); ?></legend>
			<?php echo $this->form->getLabel('title'); ?>
			<?php echo $this->form->getInput('title'); ?>
			<br />
			<?php echo $this->form->getLabel('catid'); ?>
			<?php echo $this->form->getInput('catid'); ?>
			<br />
			<?php echo $this->form->getLabel('url'); ?>
			<?php echo $this->form->getInput('url'); ?>
			<br />
			<?php if ($this->user->authorise('core.edit.state', 'com_weblinks.weblink.'.$this->item->id)): ?>
				<?php echo $this->form->getLabel('state'); ?>
				<?php echo $this->form->getInput('state'); ?>
			<?php endif; ?>
			<br />
			<?php echo $this->form->getLabel('description'); ?>
			<?php echo $this->form->getInput('description'); ?>
	</fieldset>
	<fieldset>
		<button type="button" onclick="submitbutton('weblink.save')">
			<?php echo JText::_('Save') ?>
		</button>
		<button type="button" onclick="submitbutton('weblink.cancel')">
			<?php echo JText::_('Cancel') ?>
		</button>
	</fieldset>

	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>