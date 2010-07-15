<?php
/**
 * @version		$Id: edit.php 17094 2010-05-16 09:41:53Z infograf768 $
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.calendar');
JHtml::_('behavior.formvalidation');

// Create shortcut to parameters.
$params = $this->state->get('params');
?>

<script type="text/javascript">
	function submitbutton(task) {
		if (task == 'article.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
			<?php echo $this->form->getField('text')->save(); ?>
		submitform(task);
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<div class="edit item-page<?php echo $this->escape($params->get('pageclass_sfx')); ?>">
<?php if ($params->get('show_page_heading', 1)) : ?>
<h1>
	<?php echo $this->escape($params->get('page_heading')); ?>
</h1>
<?php endif; ?>

<form action="<?php echo JRoute::_('index.php?option=com_content'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<fieldset>
		<legend><?php echo JText::_('JEDITOR'); ?></legend>

			<div class="formelm">
			<?php echo $this->form->getLabel('title'); ?>
			<?php echo $this->form->getInput('title'); ?>
			</div>

		<?php if (is_null($this->item->id)):?>
			<div class="formelm">
			<?php echo $this->form->getLabel('alias'); ?>
			<?php echo $this->form->getInput('alias'); ?>
			</div>
		<?php endif; ?>

			<div class="formelm_buttons">
			<button type="button" onclick="submitbutton('article.save')">
				<?php echo JText::_('JSAVE') ?>
			</button>
			<button type="button" onclick="submitbutton('article.cancel')">
				<?php echo JText::_('JCANCEL') ?>
			</button>
			</div>


			<?php echo $this->form->getInput('text'); ?>

	</fieldset>

	<fieldset>
		<legend><?php echo JText::_('COM_CONTENT_PUBLISHING'); ?></legend>
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
		<?php if (is_null($this->item->id)):?>
			<div class="form-note">
			<p><?php echo JText::_('COM_CONTENT_ORDERING'); ?></p>
			</div>
		<?php endif; ?>
	</fieldset>

	<fieldset>
		<legend><?php echo JText::_('JFIELD_LANGUAGE_LABEL'); ?></legend>
		<div class="formelm_area">
		<?php echo $this->form->getLabel('language'); ?>
		<?php echo $this->form->getInput('language'); ?>
		</div>
	</fieldset>

	<fieldset>
		<legend><?php echo JText::_('COM_CONTENT_METADATA'); ?></legend>
		<div class="formelm_area">
		<?php echo $this->form->getLabel('metadesc'); ?>
		<?php echo $this->form->getInput('metadesc'); ?>
		</div>
		<div class="formelm_area">
		<?php echo $this->form->getLabel('metakey'); ?>
		<?php echo $this->form->getInput('metakey'); ?>
		</div>
		<input type="hidden" name="task" value="" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</fieldset>
</form>
</div>