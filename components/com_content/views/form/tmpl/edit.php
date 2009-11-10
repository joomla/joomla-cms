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
JHtml::_('behavior.calendar');
JHtml::_('behavior.formvalidation');
?>

<script language="javascript" type="text/javascript">
function submitbutton(task) {
	if (task == 'article.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
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

<form action="<?php echo JRoute::_('index.php?option=com_content'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<fieldset>
		<legend><?php echo JText::_('Editor'); ?></legend>
		<div style="float: left;">
			<?php echo $this->form->getLabel('title'); ?>
			<?php echo $this->form->getInput('title'); ?>
		</div>
		<div style="float: right;">
			<button type="button" onclick="submitbutton('article.save')">
				<?php echo JText::_('JSave') ?>
			</button>
			<button type="button" onclick="submitbutton('article.cancel')">
				<?php echo JText::_('JCancel') ?>
			</button>
		</div>
		<div style="clear: both">
			<?php echo $this->form->getInput('text'); ?>
		</div>
	</fieldset>

	<fieldset>
		<legend><?php echo JText::_('Publishing'); ?></legend>
		<?php echo $this->form->getLabel('catid'); ?>
		<?php echo $this->form->getInput('catid'); ?>
	<br />
		<?php echo $this->form->getLabel('created_by_alias'); ?>
		<?php echo $this->form->getInput('created_by_alias'); ?>
	<br />
	<?php if ($this->user->authorise('core.edit.state', 'com_content.article.'.$this->item->id)): ?>
		<?php echo $this->form->getLabel('state'); ?>
		<?php echo $this->form->getInput('state'); ?>
	<br />
		<?php echo $this->form->getLabel('publish_up'); ?>
		<?php echo $this->form->getInput('publish_up'); ?>
	<br />
		<?php echo $this->form->getLabel('publish_down'); ?>
		<?php echo $this->form->getInput('publish_down'); ?>
	<br />
	<?php endif; ?>
		<?php echo $this->form->getLabel('access'); ?>
		<?php echo $this->form->getInput('access'); ?>
	<br />
		<?php echo $this->form->getLabel('ordering'); ?>
		<?php echo $this->form->getInput('ordering'); ?>
	</fieldset>

	<fieldset>
		<legend><?php echo JText::_('Metadata'); ?></legend>

		<?php echo $this->form->getLabel('metadesc'); ?>
		<?php echo $this->form->getInput('metadesc'); ?>
	<br />
		<?php echo $this->form->getLabel('metakey'); ?>
		<?php echo $this->form->getInput('metakey'); ?>
	</fieldset>

	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>