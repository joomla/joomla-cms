<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/** @var JoomlaupdateViewDefault $this */
?>

<fieldset>
	<?php if (!$this->getModel()->isDatabaseTypeSupported()) : ?>
		<legend>
			<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_DB_NOT_SUPPORTED'); ?>
		</legend>
		<p>
			<?php echo JText::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_DB_NOT_SUPPORTED_DESC', $this->updateInfo['latest']); ?>
		</p>
	<?php endif; ?>
	<?php if (!$this->getModel()->isPhpVersionSupported()) : ?>
		<legend>
			<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_PHP_VERSION_NOT_SUPPORTED'); ?>
		</legend>
		<p>
			<?php echo JText::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_PHP_VERSION_NOT_SUPPORTED_DESC', $this->updateInfo['latest']); ?>
		</p>
	<?php endif; ?>
	<?php if (!isset($this->updateInfo['object']->downloadurl->_data) && $this->updateInfo['installed'] < $this->updateInfo['latest'] && $this->getModel()->isPhpVersionSupported() && $this->getModel()->isDatabaseTypeSupported()) : ?>
		<legend>
			<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_NO_DOWNLOAD_URL'); ?>
		</legend>
		<p>
			<?php echo JText::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_NO_DOWNLOAD_URL_DESC', $this->updateInfo['latest']); ?>
		</p>
	<?php endif; ?>


</fieldset>
