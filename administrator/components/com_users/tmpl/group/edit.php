<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
?>

<form action="<?php echo Route::_('index.php?option=com_users&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="group-form" class="form-validate">
	<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>
	<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'details', Text::_('COM_USERS_USERGROUP_DETAILS')); ?>
		<?php echo $this->form->renderField('title'); ?>
		<?php echo $this->form->renderField('parent_id'); ?>
	<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
	<?php $this->ignore_fieldsets = array('group_details'); ?>
	<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>
	<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>

	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
