<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');

?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'application.cancel' || document.formvalidator.isValid(document.id('application-form'))) {
			Joomla.submitform(task, document.getElementById('application-form'));
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_config'); ?>" id="application-form" method="post" name="adminForm" class="form-validate">
	<div class="row-fluid">
		<!-- Begin Sidebar -->
		<div id="sidebar" class="span2">
			<div class="sidebar-nav">
				<?php echo $this->loadTemplate('navigation'); ?>
				<?php
				// Display the submenu position modules
				$this->submenumodules = JModuleHelper::getModules('submenu');
				foreach ($this->submenumodules as $submenumodule)
				{
					$output = JModuleHelper::renderModule($submenumodule);
					$params = new JRegistry;
					$params->loadString($submenumodule->params);
					echo $output;
				}
				?>
			</div>
		</div>
		<!-- End Sidebar -->
		<!-- Begin Content -->
		<div class="span10">
			<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'page-site')); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'page-site', JText::_('JSITE', true)); ?>
			<div class="row-fluid">
				<div class="span6">
					<?php echo $this->loadTemplate('site'); ?>
					<?php echo $this->loadTemplate('metadata'); ?>
				</div>
				<div class="span6">
					<?php echo $this->loadTemplate('seo'); ?>
					<?php echo $this->loadTemplate('cookie'); ?>
				</div>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'page-system', JText::_('COM_CONFIG_SYSTEM', true)); ?>
			<div class="row-fluid">
				<div class="span6">
					<?php echo $this->loadTemplate('system'); ?>
					<?php echo $this->loadTemplate('debug'); ?>
				</div>
				<div class="span6">
					<?php echo $this->loadTemplate('cache'); ?>
					<?php echo $this->loadTemplate('session'); ?>
				</div>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'page-server', JText::_('COM_CONFIG_SERVER', true)); ?>
			<div class="row-fluid">
				<div class="span6">
					<?php echo $this->loadTemplate('server'); ?>
					<?php echo $this->loadTemplate('locale'); ?>
					<?php echo $this->loadTemplate('ftp'); ?>
				</div>
				<div class="span6">
					<?php echo $this->loadTemplate('database'); ?>
					<?php echo $this->loadTemplate('mail'); ?>
				</div>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'page-permissions', JText::_('COM_CONFIG_PERMISSIONS', true)); ?>
			<div class="row-fluid">
				<?php echo $this->loadTemplate('permissions'); ?>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'page-filters', JText::_('COM_CONFIG_TEXT_FILTERS', true)); ?>
			<div class="row-fluid">
				<?php echo $this->loadTemplate('filters'); ?>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php if ($this->ftp) : ?>
				<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'page-ftp', JText::_('COM_CONFIG_FTP_SETTINGS', true)); ?>
				<?php echo $this->loadTemplate('ftplogin'); ?>
				<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php endif; ?>

			<?php echo JHtml::_('bootstrap.endTabSet'); ?>

			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
		<!-- End Content -->
	</div>
</form>
