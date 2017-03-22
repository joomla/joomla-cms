<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

// Load tooltips behavior
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.tabstate');

// Load JS message titles
JText::script('ERROR');
JText::script('WARNING');
JText::script('NOTICE');
JText::script('MESSAGE');
?>

<form action="<?php echo JRoute::_('index.php?option=com_config'); ?>" id="application-form" method="post" name="adminForm" class="form-validate" data-cancel-task="config.cancel.component">
	<div class="row">
		<!-- Begin Sidebar -->
		<div id="sidebar" class="col-md-2">
			<div class="sidebar-nav">
				<?php echo $this->loadTemplate('navigation'); ?>
				<?php
				// Display the submenu position modules
				$this->submenumodules = JModuleHelper::getModules('submenu');
				foreach ($this->submenumodules as $submenumodule)
				{
					$output = JModuleHelper::renderModule($submenumodule);
					$params = new Registry($submenumodule->params);
					echo $output;
				}
				?>
			</div>
		</div>
		<!-- End Sidebar -->
		<!-- Begin Content -->
		<div class="col-md-10">
			<ul class="nav nav-tabs">

				<li class="nav-item"><a class="nav-link active" href="#page-site" data-toggle="tab"><?php echo JText::_('JSITE'); ?></a></li>
				<li class="nav-item"><a class="nav-link" href="#page-system" data-toggle="tab"><?php echo JText::_('COM_CONFIG_SYSTEM'); ?></a></li>
				<li class="nav-item"><a class="nav-link" href="#page-server" data-toggle="tab"><?php echo JText::_('COM_CONFIG_SERVER'); ?></a></li>
				<li class="nav-item"><a class="nav-link" href="#page-filters" data-toggle="tab"><?php echo JText::_('COM_CONFIG_TEXT_FILTERS'); ?></a></li>
				<?php if ($this->ftp) : ?>
					<li class="nav-item"><a class="nav-link" href="#page-ftp" data-toggle="tab"><?php echo JText::_('COM_CONFIG_FTP_SETTINGS'); ?></a></li>
				<?php endif; ?>
				<li class="nav-item"><a class="nav-link" href="#page-permissions" data-toggle="tab"><?php echo JText::_('COM_CONFIG_PERMISSIONS'); ?></a></li>
			</ul>
			<div id="config-document" class="tab-content">
				<div id="page-site" class="tab-pane active">
					<div class="row">
						<div class="col-lg-12 col-xl-6">
							<?php echo $this->loadTemplate('site'); ?>
							<?php echo $this->loadTemplate('metadata'); ?>
						</div>
						<div class="col-lg-12 col-xl-6">
							<?php echo $this->loadTemplate('seo'); ?>
							<?php echo $this->loadTemplate('cookie'); ?>
						</div>
					</div>
				</div>
				<div id="page-system" class="tab-pane">
					<div class="row">
						<div class="col-md-12">
							<?php echo $this->loadTemplate('system'); ?>
							<?php echo $this->loadTemplate('debug'); ?>
							<?php echo $this->loadTemplate('cache'); ?>
							<?php echo $this->loadTemplate('session'); ?>
						</div>
					</div>
				</div>
				<div id="page-server" class="tab-pane">
					<div class="row">
						<div class="col-lg-12 col-xl-6">
							<?php echo $this->loadTemplate('server'); ?>
							<?php echo $this->loadTemplate('locale'); ?>
							<?php echo $this->loadTemplate('ftp'); ?>
							<?php echo $this->loadTemplate('proxy'); ?>
						</div>
						<div class="col-lg-12 col-xl-6">
							<?php echo $this->loadTemplate('database'); ?>
							<?php echo $this->loadTemplate('mail'); ?>
						</div>
					</div>
				</div>
				<div id="page-filters" class="tab-pane">
					<div class="row">
						<div class="col-md-12">
							<?php echo $this->loadTemplate('filters'); ?>
						</div>
					</div>
				</div>
				<?php if ($this->ftp) : ?>
					<div id="page-ftp" class="tab-pane">
						<div class="col-md-12">
							<?php echo $this->loadTemplate('ftplogin'); ?>
						</div>
					</div>
				<?php endif; ?>
				<div id="page-permissions" class="tab-pane">
					<div class="row-fluid">
						<?php echo $this->loadTemplate('permissions'); ?>
					</div>
				</div>
				<input type="hidden" name="task" value="">
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</div>
		<!-- End Content -->
	</div>
</form>
