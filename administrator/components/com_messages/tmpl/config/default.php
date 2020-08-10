<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
?>

<form action="<?php echo Route::_('index.php?option=com_messages&view=config'); ?>" method="post" name="adminForm" id="message-form" class="form-validate">
	<div class="col-lg-8 col-xl-6">
		<div class="card">
			<div class="card-body">
				<?php echo $this->form->renderField('lock'); ?>
				<?php echo $this->form->renderField('mail_on_new'); ?>
				<?php echo $this->form->renderField('auto_purge'); ?>
			</div>
		</div>
	</div>

	<input type="hidden" name="task" value="">
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
