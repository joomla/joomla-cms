<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

// Include the component HTML helpers.
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('behavior.formvalidator');

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();
?>

<form action="<?php echo Route::_('index.php?option=com_admin&view=profile&layout=edit&id=' . $this->item->id); ?>" method="post" name="adminForm" id="profile-form" enctype="multipart/form-data" class="form-validate">
	<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'account')); ?>

	<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'account', Text::_('COM_ADMIN_USER_ACCOUNT_DETAILS')); ?>
	<?php foreach ($this->form->getFieldset('user_details') as $field) : ?>
		<div class="control-group">
			<div class="control-label">
				<?php echo $field->label; ?>
			</div>
			<div class="controls">
				<?php if ($field->fieldname == 'password2') : ?>
					<?php // Disables autocomplete ?> <input type="password" style="display:none">
				<?php endif; ?>
				<?php echo $field->input; ?>
			</div>
		</div>
	<?php endforeach; ?>
	<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

	<?php foreach ($fieldsets as $fieldset) : ?>
		<?php
		if ($fieldset->name == 'user_details')
		{
			continue;
		}
		?>
		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', $fieldset->name, Text::_($fieldset->label)); ?>
		<?php foreach ($this->form->getFieldset($fieldset->name) as $field) : ?>
			<?php if ($field->hidden) : ?>
				<div class="control-group">
					<div class="controls"><?php echo $field->input; ?></div>
				</div>
			<?php else : ?>
				<div class="control-group">
					<div class="control-label">
						<?php echo $field->label; ?>
					</div>
					<div class="controls"><?php echo $field->input; ?></div>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
	<?php endforeach; ?>

	<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>
	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
