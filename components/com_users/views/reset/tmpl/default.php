<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.mootools');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>
<div class="reset<?php echo $this->params->get('pageclass_sfx')?>">
<?php if ($this->params->get('show_page_heading')) : ?>
<h1>
	<?php echo $this->escape($this->params->get('page_heading')); ?>
</h1>
<?php endif; ?>

<form id="member-registration" action="<?php echo JRoute::_('index.php?option=com_users&task=reset.reset_request'); ?>" method="post" class="form-validate">

	<?php
	// Iterate through the form fieldsets and display each one.
	foreach ($this->form->getFieldsets() as $fieldset):
	?>
	<fieldset>
		<dl>
		<?php foreach ($this->form->getFieldset($fieldset->name) as $name => $field): ?>
			<dt><?php echo $field->label; ?></dt>
			<dd><?php echo $field->input; ?></dd>
		<?php endforeach; ?>
		</dl>
	</fieldset>
<?php endforeach; ?>

	<button type="submit"><?php echo JText::_('BUTTON_SUBMIT'); ?></button>
	<input type="hidden" name="option" value="com_users" />
	<input type="hidden" name="task" value="reset.request" />
	<?php echo JHtml::_('form.token'); ?>
</form>
</div>