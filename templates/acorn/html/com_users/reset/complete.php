<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
?>
<div class="reset-complete<?php echo $this->pageclass_sfx?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
	<h1>
		<?php echo $this->escape($this->params->get('page_heading')); ?>
	</h1>
	<?php endif; ?>

	<form action="<?php echo JRoute::_('index.php?option=com_users&task=reset.complete'); ?>" method="post" class="form-validate form-horizontal">

		<?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
			<fieldset>
				<p class="alert alert-info">
					<span class="glyphicon glyphicon-info-sign"></span>
					<?php echo JText::_($fieldset->label); ?>
				</p>
				<?php foreach ($this->form->getFieldset($fieldset->name) as $name => $field) : ?>
					<div class="form-group">
						<div class="control-label col-sm-2">
							<?php echo $field->label; ?>
						</div>
						<div class="col-sm-10">
							<?php echo $field->input; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</fieldset>
		<?php endforeach; ?>

		<div class="col-sm-offset-2">
			<button type="submit" class="validate btn btn-primary"><?php echo JText::_('JSUBMIT'); ?></button>
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
