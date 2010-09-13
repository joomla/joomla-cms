<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

// Create shortcut to parameters.
$params = $this->state->get('params');
?>

<script type="text/javascript">
	function submitbutton(task) {
		if (task == 'weblink.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task);
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>
<div class="edit<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
<?php if ($this->params->def('show_page_heading', 1)) : ?>
<h1>
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php endif; ?>
<form action="<?php echo JRoute::_('index.php?option=com_weblinks'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<fieldset>
		<legend><?php echo JText::_('COM_WEBLINKS_LINK'); ?></legend>
			<div class="formelm">
			<?php echo $this->form->getLabel('title'); ?>
			<?php echo $this->form->getInput('title'); ?>
			</div>
			<div class="formelm">
			<?php echo $this->form->getLabel('catid'); ?>
			<?php echo $this->form->getInput('catid'); ?>
			</div>
			<div class="formelm">
			<?php echo $this->form->getLabel('url'); ?>
			<?php echo $this->form->getInput('url'); ?>
			</div>
			<?php if ($this->user->authorise('core.edit.state', 'com_weblinks.weblink')): ?>
				<div class="formelm">
				<?php echo $this->form->getLabel('state'); ?>
				<?php echo $this->form->getInput('state'); ?>
				</div>
			<?php endif; ?>
			<div class="formelm">
			<?php echo $this->form->getLabel('language'); ?>
			<?php echo $this->form->getInput('language'); ?>
			</div>
			<div class="formelm">
			<?php echo $this->form->getLabel('description'); ?>
			<?php echo $this->form->getInput('description'); ?>
			</div>
	</fieldset>

		<div class="formelm-buttons">
		<button type="button" onclick="submitbutton('weblink.save')">
			<?php echo JText::_('JSAVE') ?>
		</button>
		<button type="button" onclick="submitbutton('weblink.cancel')">
			<?php echo JText::_('JCANCEL') ?>
		</button>
		<input type="hidden" name="task" value="" />
		<?php echo JHTML::_( 'form.token' ); ?>
		</div>

</form>
</div>
