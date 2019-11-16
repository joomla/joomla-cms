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

/** @var \Joomla\Component\Joomlaupdate\Administrator\View\Joomlaupdate\Html $this */
?>

<fieldset class="options-form">
	<legend>
		<?php echo Text::_('COM_JOOMLAUPDATE_SYSTEM_CHECK'); ?>
	</legend>
	<div>
	<?php if ( !$this->getModel()->isDatabaseTypeSupported()) : ?>
		<p class="alert alert-warning">
			<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_DB_NOT_SUPPORTED'); ?>
		</p>
		<p>
			<?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_DB_NOT_SUPPORTED_DESC', '&#x200E;' . $this->updateInfo['latest']); ?>
		</p>
	<?php endif; ?>
	<?php if (!$this->getModel()->isPhpVersionSupported()) : ?>
		<p class="alert alert-warning">
			<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_PHP_VERSION_NOT_SUPPORTED'); ?>
		</p>
		<p>
			<?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_PHP_VERSION_NOT_SUPPORTED_DESC', '&#x200E;' . $this->updateInfo['latest']); ?>
		</p>
	<?php endif; ?>
	<?php if (!isset($this->updateInfo['object']->downloadurl->_data) && $this->updateInfo['installed'] < $this->updateInfo['latest'] && $this->getModel()->isPhpVersionSupported() && $this->getModel()->isDatabaseTypeSupported()) : ?>
		<p class="alert alert-warning">
			<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_NO_DOWNLOAD_URL'); ?>
		</p>
		<p>
			<?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_NO_DOWNLOAD_URL_DESC', '&#x200E;' . $this->updateInfo['latest']); ?>
		</p>
	<?php endif; ?>
	</div>
</fieldset>
