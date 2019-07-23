<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mailto
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.core');
HTMLHelper::_('behavior.keepalive');

Text::script('COM_MAILTO_EMAIL_ERR_NOINFO', true);

HTMLHelper::_('script', 'com_mailto/mailto-default.js', ['version' => 'auto', 'relative' => true]);
?>

<div id="mailto-window" class="com-mailto p-2">
	<h2>
		<?php echo Text::_('COM_MAILTO_EMAIL_TO_A_FRIEND'); ?>
	</h2>
	<div class="com-mailto__close mailto-close">
		<a href="#" class="close-mailto">
			<span>
				<?php echo Text::_('COM_MAILTO_CLOSE_WINDOW'); ?>
			</span>
		</a>
	</div>

	<form action="<?php echo Route::_('index.php?option=com_mailto&task=send'); ?>" method="post" class="form-validate com-mailto__form">
		<fieldset>
			<?php foreach ($this->form->getFieldset('') as $field) : ?>
				<?php /** @var \Joomla\CMS\Form\FormField $field  */ ?>
				<?php if (!$field->hidden) : ?>
					<?php echo $field->renderField(['class' => 'com-mailto__' . $field->name]); ?>
				<?php endif; ?>
			<?php endforeach; ?>
			<div class="com-mailto__submit control-group">
				<button type="submit" class="com-mailto__send btn btn-success">
					<?php echo Text::_('COM_MAILTO_SEND'); ?>
				</button>
				<button type="button" class="com-mailto__cancel btn btn-danger close-mailto">
					<?php echo Text::_('COM_MAILTO_CANCEL'); ?>
				</button>
			</div>
		</fieldset>

		<input type="hidden" name="layout" value="<?php echo htmlspecialchars($this->getLayout(), ENT_COMPAT, 'UTF-8'); ?>">
		<input type="hidden" name="option" value="com_mailto">
		<input type="hidden" name="task" value="send">
		<input type="hidden" name="tmpl" value="component">
		<input type="hidden" name="link" value="<?php echo $this->link; ?>">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
