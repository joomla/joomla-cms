<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mailto
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

HTMLHelper::_('behavior.core');
HTMLHelper::_('behavior.keepalive');

Text::script('COM_MAILTO_EMAIL_ERR_NOINFO', true);

HTMLHelper::_('script', 'com_mailto/mailto-default.js', ['relative' => true, 'version' => 'auto']);

$data = $this->get('data');
?>

<div id="mailto-window" class="com-mailto p-2">
	<h2>
		<?php echo Text::_('COM_MAILTO_EMAIL_TO_A_FRIEND'); ?>
	</h2>
	<div class="com-mailto__close mailto-close">
		<a title="<?php echo Text::_('COM_MAILTO_CLOSE_WINDOW'); ?>" href="#" class="close-mailto">
		 <span>
             <?php echo Text::_('COM_MAILTO_CLOSE_WINDOW'); ?>
         </span></a>
	</div>

	<form action="<?php echo Uri::base() ?>index.php" id="mailtoForm" method="post" class="com-mailto__form">
		<div class="com-mailto__emailto control-group">
			<div class="control-label">
				<label for="mailto_field">
                    <?php echo Text::_('COM_MAILTO_EMAIL_TO'); ?>
                </label>
			</div>
			<div class="controls">
				<input type="text" id="mailto_field" name="mailto" class="form-control" value="<?php echo $this->escape($data->mailto); ?>">
			</div>
		</div>
		<div class="com-mailto__sender control-group">
			<div class="control-label">
				<label for="sender_field">
                    <?php echo Text::_('COM_MAILTO_SENDER'); ?>
                </label>
			</div>
			<div class="controls">
				<input type="text" id="sender_field" name="sender" class="form-control" value="<?php echo $this->escape($data->sender); ?>">
			</div>
		</div>
		<div class="com-mailto__your-email control-group">
			<div class="control-label">
				<label for="from_field">
                    <?php echo Text::_('COM_MAILTO_YOUR_EMAIL'); ?>
                </label>
			</div>
			<div class="controls">
				<input type="text" id="from_field" name="from" class="form-control" value="<?php echo $this->escape($data->from); ?>">
			</div>
		</div>
		<div class="com-mailto__subject control-group">
				<div class="control-label">
			<label for="subject_field">
                <?php echo Text::_('COM_MAILTO_SUBJECT'); ?>
            </label>
			</div>
			<div class="controls">
				<input type="text" id="subject_field" name="subject" class="form-control" value="<?php echo $this->escape($data->subject); ?>">
			</div>
		</div>
		<div class="com-mailto__submit control-group">
			<button type="button" class="com-mailto__cancel btn btn-danger close-mailto">
				<?php echo Text::_('COM_MAILTO_CANCEL'); ?>
			</button>
			<button type="submit" class="com-mailto__send btn btn-success">
				<?php echo Text::_('COM_MAILTO_SEND'); ?>
			</button>
		</div>

		<input type="hidden" name="layout" value="<?php echo htmlspecialchars($this->getLayout(), ENT_COMPAT, 'UTF-8'); ?>">
		<input type="hidden" name="option" value="com_mailto">
		<input type="hidden" name="task" value="send">
		<input type="hidden" name="tmpl" value="component">
		<input type="hidden" name="link" value="<?php echo $data->link; ?>">
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
