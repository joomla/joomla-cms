<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var JoomlaupdateViewDefault $this */
?>

<fieldset class="options-form">
	<legend>
		<?php echo Text::_('COM_JOOMLAUPDATE_LIVE_UPDATE'); ?>
	</legend>
	<p class="alert alert-warning">
		<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_NO_LIVE_UPDATE'); ?>
	</p>
	<p>
		<?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_NO_LIVE_UPDATE_DESC'); ?>
	</p>
</fieldset>
