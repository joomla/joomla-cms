<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\WebAsset\WebAssetManager;
use Joomla\Component\Joomlaupdate\Administrator\View\Joomlaupdate\HtmlView;

/** @var HtmlView $this */

/** @var WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('core')
	->useScript('com_joomlaupdate.default')
	->useScript('bootstrap.popover');

Text::script('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE', true);
Text::script('JYES');
Text::script('JNO');
Text::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NO_COMPATIBILITY_INFORMATION');
Text::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_WARNING_UNKNOWN');
Text::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_SERVER_ERROR');
Text::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_POTENTIALLY_DANGEROUS_PLUGIN');
Text::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_POTENTIALLY_DANGEROUS_PLUGIN_DESC');
Text::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_POTENTIALLY_DANGEROUS_PLUGIN_LIST');
Text::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_POTENTIALLY_DANGEROUS_PLUGIN_CONFIRM_MESSAGE');
Text::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_HELP');

$latestJoomlaVersion = $this->updateInfo['latest'];
$currentJoomlaVersion = isset($this->updateInfo['current']) ? $this->updateInfo['current'] : JVERSION;
?>

<div id="joomlaupdate-wrapper" class="main-card mt-3" data-joomla-target-version="<?php echo $latestJoomlaVersion; ?>" data-joomla-current-version="<?php echo $currentJoomlaVersion; ?>">
	<?php if (true) : ?>
		<?php echo HTMLHelper::_('uitab.startTabSet', 'joomlaupdate-tabs', array('active' => $this->shouldDisplayPreUpdateCheck() ? 'pre-update-check' : 'online-update')); ?>
		<?php echo HTMLHelper::_('uitab.addTab', 'joomlaupdate-tabs', 'online-update', Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_TAB_ONLINE')); ?>
	<?php endif; ?>

	<form enctype="multipart/form-data" action="index.php" method="post" id="adminForm">

			<?php if (!isset($this->updateInfo['object']->downloadurl->_data)
				|| !$this->getModel()->isDatabaseTypeSupported()
				|| !$this->getModel()->isPhpVersionSupported()) : ?>
				<?php // If we have no download URL or our PHP version or our DB type is not supported then we can't reinstall or update ?>
				<?php echo $this->loadTemplate('nodownload'); ?>
			<?php elseif (!$this->updateInfo['hasUpdate']) : ?>
				<?php // If we have no update but we have a downloadurl then we can reinstall the core ?>
				<?php echo $this->loadTemplate('reinstall'); ?>
			<?php else : ?>
				<?php // Ok let's show the update template ?>
				<?php echo $this->loadTemplate('update'); ?>
			<?php endif; ?>

		<input type="hidden" name="task" value="update.download">
		<input type="hidden" name="option" value="com_joomlaupdate">

		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
