<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>
<fieldset class="options-form">
	<legend><?php echo Text::_('COM_CONFIG_FTP_DETAILS'); ?></legend>
	<p><?php echo Text::_('COM_CONFIG_FTP_DETAILS_TIP'); ?></p>
	<?php if ($this->ftp instanceof Exception) : ?>
	<?php /** @var Exception $this */ ?>
		<p><?php echo Text::_($this->ftp->message); ?></p>
	<?php endif; ?>
		<div>
			<div class="control-group">
				<div class="control-label">
					<label for="username"><?php echo Text::_('JGLOBAL_USERNAME'); ?></label>
				</div>
				<div class="controls">
					<input type="text" id="username" name="username" class="form-control" size="70" value="">
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<label for="password"><?php echo Text::_('JGLOBAL_PASSWORD'); ?></label>
				</div>
				<div class="controls">
					<input type="password" id="password" name="password" class="form-control" size="70" value="">
				</div>
			</div>
		</div>
</fieldset>
