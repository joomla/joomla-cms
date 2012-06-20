<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<div id="filter-bar" class="btn-toolbar">
	<div class="btn-group pull-right">
		<a data-toggle="collapse" data-target="#filters" class="btn"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?> <span class="caret"></span></a>
	</div>
	<?php foreach($this->form->getFieldSet('search') as $field): ?>
		<?php echo $field->input; ?>
	<?php endforeach; ?>
</div>
<div class="clearfix"></div>
<div class="collapse" id="filters">
	<div class="filter-select well">
		<?php foreach($this->form->getFieldSet('select') as $field): ?>
			<?php if (!$field->hidden): ?>
				<?php echo $field->label; ?>
			<?php endif; ?>
			<?php echo $field->input; ?>
		<?php endforeach; ?>
	</div>
</div>
