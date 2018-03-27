<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;
?>
<fieldset title="<?php echo Text::_('COM_CONFIG_FTP_DETAILS'); ?>">
	<legend><?php echo Text::_('COM_CONFIG_FTP_DETAILS'); ?></legend>
	<?php echo Text::_('COM_CONFIG_FTP_DETAILS_TIP'); ?>
	<?php if ($this->ftp instanceof Exception) : ?>
	<?php /** @var Exception $this */ ?>
		<p><?php echo Text::_($this->ftp->message); ?></p>
	<?php endif; ?>
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
</fieldset>
