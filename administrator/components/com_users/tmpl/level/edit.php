<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

?>

<form action="<?php echo Route::_('index.php?option=com_users&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="level-form" class="form-validate">
	<fieldset class="options-form mt-4">
		<legend><?php echo Text::_('COM_USERS_LEVEL_DETAILS'); ?></legend>
		<div>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('title'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('title'); ?>
			</div>
		</div>
		</div>
	</fieldset>

	<fieldset class="options-form">
		<legend><?php echo Text::_('COM_USERS_USER_GROUPS_HAVING_ACCESS'); ?></legend>
		<div>
		<?php echo HTMLHelper::_('access.usergroups', 'jform[rules]', $this->item->rules, true); ?>
		</div>
	</fieldset>
	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
