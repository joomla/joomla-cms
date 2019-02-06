<?php
/**
 * @package	 Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license	 GNU General Public License version 2 or later; see LICENSE.txt
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

// Load JS message titles
Text::script('ERROR');
Text::script('WARNING');
Text::script('NOTICE');
Text::script('MESSAGE');
?>

<form action="<?php echo Route::_('index.php?option=com_config'); ?>" id="application-form" method="post" name="adminForm" class="form-validate" data-cancel-task="config.cancel.component">
	<div class="row">
		<div id="sidebar" class="col-md-2">
			<div class="sidebar-nav">
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
		<div class="col-md-10">
		<?php echo HTMLHelper::_('uitab.startTabSet', 'gc_config', array('active' => 'config-document')); ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'gc_config', 'config-document', Text::_('JSITE')); ?>
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
			<?php echo JHtml::_('uitab.endTab'); ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'gc_config', 'page-system', Text::_('COM_CONFIG_SYSTEM')); ?>
				<div class="row">
					<div class="col-md-12">
						<?php echo $this->loadTemplate('system'); ?>
						<?php echo $this->loadTemplate('debug'); ?>
						<?php echo $this->loadTemplate('cache'); ?>
						<?php echo $this->loadTemplate('session'); ?>
					</div>
				</div>
			<?php echo JHtml::_('uitab.endTab'); ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'gc_config', 'page-server', Text::_('COM_CONFIG_SERVER')); ?>
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
			<?php echo JHtml::_('uitab.endTab'); ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'gc_config', 'page-filters', Text::_('COM_CONFIG_TEXT_FILTERS')); ?>
				<div class="row">
					<div class="col-md-12">
						<?php echo $this->loadTemplate('filters'); ?>
					</div>
				</div>
			<?php echo JHtml::_('uitab.endTab'); ?>
			<?php if ($this->ftp) : ?>
				<?php echo HTMLHelper::_('uitab.addTab', 'gc_config', 'page-ftp', Text::_('COM_CONFIG_FTP_SETTINGS')); ?>
					<div class="col-md-12">
						<?php echo $this->loadTemplate('ftplogin'); ?>
					</div>
				<?php echo JHtml::_('uitab.endTab'); ?>
			<?php endif; ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'gc_config', 'page-permissions', Text::_('COM_CONFIG_PERMISSIONS')); ?>
				<div class="row">
					<?php echo $this->loadTemplate('permissions'); ?>
				</div>
			<?php echo JHtml::_('uitab.endTab'); ?>
			<input type="hidden" name="task" value="">
			<?php echo HTMLHelper::_('form.token'); ?>
		<?php echo JHtml::_('uitab.endTabSet'); ?>
		</div>
	</div>
</form>
