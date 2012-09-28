<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<fieldset id="filter-bar">
	<div class="filter-search">
		<?php foreach($this->form->getFieldSet('search') as $field): ?>
			<?php /* remove "onchange" action for accessibility reasons*/?>
			<?php $this->form->setFieldAttribute($field->fieldname, 'onchange', '', 'filters');?>
			<?php if (!$field->hidden): ?>
				<?php echo $field->label; ?>
			<?php endif; ?>
			<?php echo $field->input; ?>
		<?php endforeach; ?>
	</div>

	<div class="filter-select">
		<?php foreach($this->form->getFieldSet('select') as $field): ?>
			<?php /* remove "onchange" action for accessibility reasons*/?>
			<?php $this->form->setFieldAttribute($field->fieldname, 'onchange', '', 'filters');?>
			<?php if (!$field->hidden): ?>
				<?php echo $field->label; ?>
			<?php endif; ?>
			<?php echo $field->input; ?>
		<?php endforeach; ?>
		<button type="submit" id="filter-go">
				<?php echo JText::_('JSUBMIT'); ?></button>
	</div>
</fieldset>
<div class="clr"></div>
