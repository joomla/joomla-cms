<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');

// Load JS message titles
Text::script('ERROR');
Text::script('WARNING');
Text::script('NOTICE');
Text::script('MESSAGE');
?>

<form action="<?php echo Route::_('index.php?option=com_config'); ?>" id="application-form" method="post" name="adminForm" class="main-card form-validate">
	<div class="row main-card-columns">
		<div id="sidebar" class="col-md-3">
			<button class="btn btn-sm btn-secondary my-2 options-menu d-md-none" type="button" data-bs-toggle="collapse" data-bs-target=".sidebar-nav" aria-controls="sidebar-nav" aria-expanded="false" aria-label="<?php echo Text::_('JTOGGLE_SIDEBAR_MENU'); ?>">
				<span class="icon-align-justify" aria-hidden="true"></span>
				<?php echo Text::_('JTOGGLE_SIDEBAR_MENU'); ?>
			</button>
			<div id="sidebar-nav" class="sidebar-nav">
				<?php echo $this->loadTemplate('navigation'); ?>
			</div>
		</div>
		<div class="col-md-9">
			<?php echo HTMLHelper::_('uitab.startTabSet', 'configTabs', ['active' => 'page-site', 'recall' => true, 'breakpoint' => 768]); ?>
				<?php echo HTMLHelper::_('uitab.addTab', 'configTabs', 'page-site', Text::_('JSITE')); ?>
					<?php echo $this->loadTemplate('site'); ?>
					<?php echo $this->loadTemplate('metadata'); ?>
					<?php echo $this->loadTemplate('seo'); ?>
					<?php echo $this->loadTemplate('cookie'); ?>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>

				<?php echo HTMLHelper::_('uitab.addTab', 'configTabs', 'page-system', Text::_('COM_CONFIG_SYSTEM')); ?>
					<?php echo $this->loadTemplate('debug'); ?>
					<?php echo $this->loadTemplate('cache'); ?>
					<?php echo $this->loadTemplate('session'); ?>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>

				<?php echo HTMLHelper::_('uitab.addTab', 'configTabs', 'page-server', Text::_('COM_CONFIG_SERVER')); ?>
					<?php echo $this->loadTemplate('server'); ?>
					<?php echo $this->loadTemplate('locale'); ?>
					<?php echo $this->loadTemplate('webservices'); ?>
					<?php echo $this->loadTemplate('proxy'); ?>
					<?php echo $this->loadTemplate('database'); ?>
					<?php echo $this->loadTemplate('mail'); ?>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>

				<?php echo HTMLHelper::_('uitab.addTab', 'configTabs', 'page-logging', Text::_('COM_CONFIG_LOGGING')); ?>
					<?php echo $this->loadTemplate('logging'); ?>
					<?php echo $this->loadTemplate('logging_custom'); ?>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>

				<?php echo HTMLHelper::_('uitab.addTab', 'configTabs', 'page-filters', Text::_('COM_CONFIG_TEXT_FILTERS')); ?>
					<?php echo $this->loadTemplate('filters'); ?>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>

				<?php echo HTMLHelper::_('uitab.addTab', 'configTabs', 'page-permissions', Text::_('COM_CONFIG_PERMISSIONS')); ?>
					<?php echo $this->loadTemplate('permissions'); ?>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>
			<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

			<input type="hidden" name="task" value="">
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
	</div>
</form>
