<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/** @var JoomlaupdateViewDefault $this */
?>

<fieldset>
	<legend>
		<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_NO_DOWNLOAD_URL'); ?>
	</legend>
	<p>
		<?php echo JText::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_NO_DOWNLOAD_URL_DESC', $this->updateInfo['latest']); ?>
	</p>
</fieldset>
