<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;

// Include the HTML helpers.
HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
?>

<form action="<?php echo Route::_('index.php?option=com_redirect&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="link-form" class="form-validate">
	<fieldset>
		<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'basic')); ?>

			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'basic', empty($this->item->id) ? Text::_('COM_REDIRECT_NEW_LINK') : Text::sprintf('COM_REDIRECT_EDIT_LINK', $this->item->id)); ?>
				<?php echo $this->form->renderField('old_url'); ?>
				<?php echo $this->form->renderField('new_url'); ?>
				<?php echo $this->form->renderField('published'); ?>
				<?php echo $this->form->renderField('comment'); ?>
				<?php echo $this->form->renderField('id'); ?>
				<?php echo $this->form->renderField('created_date'); ?>
				<?php echo $this->form->renderField('modified_date'); ?>
				<?php if (ComponentHelper::getParams('com_redirect')->get('mode')) : ?>
					<?php echo $this->form->renderFieldset('advanced'); ?>
				<?php endif; ?>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
		<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>

		<input type="hidden" name="task" value="">
		<?php echo HTMLHelper::_('form.token'); ?>
	</fieldset>
</form>
