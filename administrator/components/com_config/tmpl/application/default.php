<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

// Load tooltips behavior
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.tabstate');

// Load JS message titles
Text::script('ERROR');
Text::script('WARNING');
Text::script('NOTICE');
Text::script('MESSAGE');
?>

<form action="<?php echo Route::_('index.php?option=com_config'); ?>" id="application-form" method="post" name="adminForm" class="form-validate" data-cancel-task="config.cancel.component">
	<div class="row">
		<!-- Begin Sidebar -->
		<div id="sidebar" class="col-md-3">
			<button class="btn btn-sm btn-secondary my-2 options-menu d-md-none" type="button" data-toggle="collapse" data-target=".sidebar-nav" aria-controls="sidebar-nav" aria-expanded="false" aria-label="<?php echo Text::_('TPL_ATUM_TOGGLE_SIDEBAR'); ?>">
				<span class="fas fa-align-justify" aria-hidden="true"></span>
				<?php echo Text::_('TPL_ATUM_TOGGLE_SIDEBAR'); ?>
			</button>
			<div class="sidebar-nav bg-light p-2 my-2">
				<?php echo $this->loadTemplate('navigation'); ?>
				<?php
				// Display the submenu position modules
				$this->submenumodules = ModuleHelper::getModules('submenu');
				foreach ($this->submenumodules as $submenumodule)
				{
					$output = ModuleHelper::renderModule($submenumodule);
					$params = new Registry($submenumodule->params);
					echo $output;
				}
				?>
			</div>
		</div>
		<!-- End Sidebar -->
		<!-- Begin Content -->
		<div class="col-md-9 mt-2">
			<ul class="nav nav-tabs">
				<li class="nav-item"><a class="nav-link active" href="#page-site" data-toggle="tab"><?php echo Text::_('JSITE'); ?></a></li>
				<li class="nav-item"><a class="nav-link" href="#page-system" data-toggle="tab"><?php echo Text::_('COM_CONFIG_SYSTEM'); ?></a></li>
				<li class="nav-item"><a class="nav-link" href="#page-server" data-toggle="tab"><?php echo Text::_('COM_CONFIG_SERVER'); ?></a></li>
				<li class="nav-item"><a class="nav-link" href="#page-filters" data-toggle="tab"><?php echo Text::_('COM_CONFIG_TEXT_FILTERS'); ?></a></li>
				<?php if ($this->ftp) : ?>
					<li class="nav-item"><a class="nav-link" href="#page-ftp" data-toggle="tab"><?php echo Text::_('COM_CONFIG_FTP_SETTINGS'); ?></a></li>
				<?php endif; ?>
				<li class="nav-item"><a class="nav-link" href="#page-permissions" data-toggle="tab"><?php echo Text::_('COM_CONFIG_PERMISSIONS'); ?></a></li>
			</ul>
			<div id="config-document" class="tab-content">
				<div id="page-site" class="tab-pane active">
					<?php echo $this->loadTemplate('site'); ?>
					<?php echo $this->loadTemplate('metadata'); ?>
					<?php echo $this->loadTemplate('seo'); ?>
					<?php echo $this->loadTemplate('cookie'); ?>
				</div>
				<div id="page-system" class="tab-pane">
					<?php echo $this->loadTemplate('system'); ?>
					<?php echo $this->loadTemplate('debug'); ?>
					<?php echo $this->loadTemplate('cache'); ?>
					<?php echo $this->loadTemplate('session'); ?>
				</div>
				<div id="page-server" class="tab-pane">
					<?php echo $this->loadTemplate('server'); ?>
					<?php echo $this->loadTemplate('locale'); ?>
					<?php echo $this->loadTemplate('ftp'); ?>
					<?php echo $this->loadTemplate('proxy'); ?>
					<?php echo $this->loadTemplate('database'); ?>
					<?php echo $this->loadTemplate('mail'); ?>
				</div>
				<div id="page-filters" class="tab-pane">
					<?php echo $this->loadTemplate('filters'); ?>
				</div>
				<?php if ($this->ftp) : ?>
					<div id="page-ftp" class="tab-pane">
						<?php echo $this->loadTemplate('ftplogin'); ?>
					</div>
				<?php endif; ?>
				<div id="page-permissions" class="tab-pane">
					<?php echo $this->loadTemplate('permissions'); ?>
				</div>
				<input type="hidden" name="task" value="">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
		<!-- End Content -->
	</div>
</form>
