<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

// Load tooltips behavior
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

// Load JS message titles
Text::script('ERROR');
Text::script('WARNING');
Text::script('NOTICE');
Text::script('MESSAGE');
?>

<form action="<?php echo Route::_('index.php?option=com_config'); ?>" id="application-form" method="post" name="adminForm" class="form-validate">
	<div class="row">
		<!-- Begin Sidebar -->
		<div id="sidebar" class="col-md-3">
			<button class="btn btn-sm btn-secondary my-2 options-menu d-md-none" type="button" data-toggle="collapse" data-target=".sidebar-nav" aria-controls="sidebar-nav" aria-expanded="false" aria-label="<?php echo Text::_('JTOGGLE_SIDEBAR_MENU'); ?>">
				<span class="fas fa-align-justify" aria-hidden="true"></span>
				<?php echo Text::_('JTOGGLE_SIDEBAR_MENU'); ?>
			</button>
			<div class="sidebar-nav bg-light p-2 my-2">
				<?php echo $this->loadTemplate('navigation'); ?>
			</div>
		</div>
		<!-- End Sidebar -->
		<!-- Begin Content -->
		<div class="col-md-9 mt-2">
			<?php echo HTMLHelper::_('uitab.startTabSet', 'configTabs', array('active' => 'page-site')); ?>
				<?php echo HTMLHelper::_('uitab.addTab', 'configTabs', 'page-site', Text::_('JSITE')); ?>
					<?php echo $this->loadTemplate('site'); ?>
					<?php echo $this->loadTemplate('metadata'); ?>
					<?php echo $this->loadTemplate('seo'); ?>
					<?php echo $this->loadTemplate('cookie'); ?>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>

				<?php echo HTMLHelper::_('uitab.addTab', 'configTabs', 'page-system', Text::_('COM_CONFIG_SYSTEM')); ?>
					<?php echo $this->loadTemplate('system'); ?>
					<?php echo $this->loadTemplate('debug'); ?>
					<?php echo $this->loadTemplate('cache'); ?>
					<?php echo $this->loadTemplate('session'); ?>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>

				<?php echo HTMLHelper::_('uitab.addTab', 'configTabs', 'page-server', Text::_('COM_CONFIG_SERVER')); ?>
					<?php echo $this->loadTemplate('server'); ?>
					<?php echo $this->loadTemplate('locale'); ?>
					<?php echo $this->loadTemplate('ftp'); ?>
					<?php echo $this->loadTemplate('proxy'); ?>
					<?php echo $this->loadTemplate('database'); ?>
					<?php echo $this->loadTemplate('mail'); ?>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>

				<?php echo HTMLHelper::_('uitab.addTab', 'configTabs', 'page-filters', Text::_('COM_CONFIG_TEXT_FILTERS')); ?>
					<?php echo $this->loadTemplate('filters'); ?>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>

				<?php if ($this->ftp) : ?>
					<?php echo HTMLHelper::_('uitab.addTab', 'configTabs', 'page-ftp', Text::_('COM_CONFIG_FTP_SETTINGS')); ?>
						<?php echo $this->loadTemplate('ftplogin'); ?>
					<?php echo HTMLHelper::_('uitab.endTab'); ?>
				<?php endif; ?>

				<?php echo HTMLHelper::_('uitab.addTab', 'configTabs', 'page-permissions', Text::_('COM_CONFIG_PERMISSIONS')); ?>
					<?php echo $this->loadTemplate('permissions'); ?>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>
			<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

			<input type="hidden" name="task" value="">
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
		<!-- End Content -->
	</div>
</form>
