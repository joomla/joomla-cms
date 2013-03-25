<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('metadesc'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('metadesc'); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo $this->form->getLabel('metakey'); ?>
	</div>
	<div class="controls">
		<?php echo $this->form->getInput('metakey'); ?>
	</div>
</div>
<?php foreach ($this->form->getGroup('metadata') as $field) : ?>
<div class="control-group">
	<div class="control-label">
		<?php if (!$field->hidden) : ?>
			<?php echo $field->label; ?>
		<?php endif; ?>
	</div>
	<div class="controls">
		<?php echo $field->input; ?>
	</div>
</div>
<?php endforeach; ?>
