<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

// Include the component HTML helpers.
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('behavior.formvalidator');
?>

<form action="<?php echo Route::_('index.php?option=com_users&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="group-form" class="form-validate">
	<fieldset>
		<legend><?php echo Text::_('COM_USERS_USERGROUP_DETAILS'); ?></legend>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('title'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('title'); ?>
			</div>
		</div>
		<div class="control-group">
			<?php $parent_id = $this->form->getField('parent_id'); ?>
			<?php if (!$parent_id->hidden) : ?>
				<div class="control-label">
					<?php echo $parent_id->label; ?>
				</div>
			<?php endif; ?>
			<div class="controls">
				<?php echo $parent_id->input; ?>
			</div>
		</div>
	</fieldset>
	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
