<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>
<fieldset class="option-fieldset options-form">

	<legend><?php echo Text::_('COM_INSTALLER_MSG_DESCFTPTITLE'); ?></legend>

	<div class="alert alert-info">
		<span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
		<?php echo Text::_('COM_INSTALLER_MSG_DESCFTP'); ?>
	</div>

	<?php if ($this->ftp instanceof Exception) : ?>
		<p><?php echo Text::_($this->ftp->getMessage()); ?></p>
	<?php endif; ?>

	<div>
		<div class="control-group">
			<div class="control-label">
				<label id="username" for="username"><?php echo Text::_('JGLOBAL_USERNAME'); ?></label>
			</div>
			<div class="controls">
				<input type="text" name="username" id="username" class="form-control">
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
				<label id="password-lbl" for="password"><?php echo Text::_('JGLOBAL_PASSWORD'); ?></label>
			</div>
			<div class="controls">
				<input type="password" name="password" id="password" class="form-control">
			</div>
		</div>
	</div>
</fieldset>
