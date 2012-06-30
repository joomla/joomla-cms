<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Load tooltips behavior
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.switcher');
JHtml::_('behavior.tooltip');

// Load submenu template, using element id 'submenu' as needed by behavior.switcher
$this->document->setBuffer($this->loadTemplate('navigation'), 'modules', 'submenu');

?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'application.cancel' || document.formvalidator.isValid(document.id('application-form'))) {
			Joomla.submitform(task, document.getElementById('application-form'));
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_config');?>" id="application-form" method="post" name="adminForm" class="form-validate">
	<?php if ($this->ftp) : ?>
		<?php echo $this->loadTemplate('ftplogin'); ?>
	<?php endif; ?>
	<div id="config-document">
		<div id="page-site" class="tab">
			<div class="noshow">
				<div class="width-60 fltlft">
					<?php echo $this->loadTemplate('site'); ?>
					<?php echo $this->loadTemplate('metadata'); ?>
				</div>
				<div class="width-40 fltrt">
					<?php echo $this->loadTemplate('seo'); ?>
					<?php echo $this->loadTemplate('cookie'); ?>
				</div>
			</div>
		</div>
		<div id="page-system" class="tab">
			<div class="noshow">
				<div class="width-60 fltlft">
					<?php echo $this->loadTemplate('system'); ?>
				</div>
				<div class="width-40 fltrt">
					<?php echo $this->loadTemplate('debug'); ?>
					<?php echo $this->loadTemplate('cache'); ?>
					<?php echo $this->loadTemplate('session'); ?>
				</div>
			</div>
		</div>
		<div id="page-server" class="tab">
			<div class="noshow">
				<div class="width-60 fltlft">
					<?php echo $this->loadTemplate('server'); ?>
					<?php echo $this->loadTemplate('locale'); ?>
					<?php echo $this->loadTemplate('ftp'); ?>
				</div>
				<div class="width-40 fltrt">
					<?php echo $this->loadTemplate('database'); ?>
					<?php echo $this->loadTemplate('mail'); ?>
				</div>
			</div>
		</div>
		<div id="page-permissions" class="tab">
			<div class="noshow">
				<?php echo $this->loadTemplate('permissions'); ?>
			</div>
		</div>
		<div id="page-filters" class="tab">
			<div class="noshow">
				<?php echo $this->loadTemplate('filters'); ?>
			</div>
		</div>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
	<div class="clr"></div>
</form>
