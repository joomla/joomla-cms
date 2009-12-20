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

<div class="page<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
<?php if ($this->params->get('show_page_title', 1)) : ?>
<h1>
	<?php if ($this->escape($this->params->get('page_heading'))) :?>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	<?php else : ?>
		<?php echo $this->escape($this->params->get('page_title')); ?>
	<?php endif; ?>
</h1>
<?php endif; ?>

<form action="<?php echo JRoute::_('index.php?option=com_content'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<fieldset>
		<legend><?php echo JText::_('Editor'); ?></legend>

			<div class="formelm">
			<?php echo $this->form->getLabel('title'); ?>
			<?php echo $this->form->getInput('title'); ?>
			</div>

           	<div class="formelm_buttons">
			<button type="button" onclick="submitbutton('article.save')">
				<?php echo JText::_('JSave') ?>
			</button>
			<button type="button" onclick="submitbutton('article.cancel')">
				<?php echo JText::_('JCancel') ?>
			</button>
			</div>


			<?php echo $this->form->getInput('text'); ?>

	</fieldset>

	<fieldset>
		<legend><?php echo JText::_('Publishing'); ?></legend>
		<div class="formelm">
		<?php echo $this->form->getLabel('catid'); ?>
		<?php echo $this->form->getInput('catid'); ?>
		</div>
        <div class="formelm">
		<?php echo $this->form->getLabel('created_by_alias'); ?>
		<?php echo $this->form->getInput('created_by_alias'); ?>
		</div>

	<?php if ($this->user->authorise('core.edit.state', 'com_content.article.'.$this->item->id)): ?>
	    <div class="formelm">
		<?php echo $this->form->getLabel('state'); ?>
		<?php echo $this->form->getInput('state'); ?>
		</div>
        <div class="formelm">
		<?php echo $this->form->getLabel('publish_up'); ?>
		<?php echo $this->form->getInput('publish_up'); ?>
        </div>
        <div class="formelm">
		<?php echo $this->form->getLabel('publish_down'); ?>
		<?php echo $this->form->getInput('publish_down'); ?>
		</div>

	<?php endif; ?>
	    <div class="formelm">
		<?php echo $this->form->getLabel('access'); ?>
		<?php echo $this->form->getInput('access'); ?>
		</div>
        <div class="formelm">
		<?php echo $this->form->getLabel('ordering'); ?>
		<?php echo $this->form->getInput('ordering'); ?>
		</div>
	</fieldset>

	<fieldset>
		<legend><?php echo JText::_('Metadata'); ?></legend>
        <div class="formelm_area">
		<?php echo $this->form->getLabel('metadesc'); ?>
		<?php echo $this->form->getInput('metadesc'); ?>
	    </div>
	    <div class="formelm_area">
		<?php echo $this->form->getLabel('metakey'); ?>
		<?php echo $this->form->getInput('metakey'); ?>
		</div>
	</fieldset>

	<input type="hidden" name="task" value="" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
</div>