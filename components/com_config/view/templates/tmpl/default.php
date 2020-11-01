<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
$user = JFactory::getUser();

JFactory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function(task)
	{
		if (task == 'config.cancel' || document.formvalidator.isValid(document.getElementById('templates-form')))
		{
			Joomla.submitform(task, document.getElementById('templates-form'));
		}
	}
");
?>

<form action="<?php echo JRoute::_('index.php?option=com_config'); ?>" method="post" name="adminForm" id="templates-form" class="form-validate">

	<div class="row-fluid">
		<!-- Begin Content -->

		<div class="btn-toolbar" role="toolbar" aria-label="<?php echo JText::_('JTOOLBAR'); ?>">
			<div class="btn-group">
				<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('config.save.templates.apply')">
					<span class="icon-ok"></span> <?php echo JText::_('JSAVE') ?>
				</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn" onclick="Joomla.submitbutton('config.cancel')">
					<span class="icon-cancel"></span> <?php echo JText::_('JCANCEL') ?>
				</button>
			</div>
		</div>

		<hr class="hr-condensed" />

		<div id="page-site" class="tab-pane active">
			<div class="row-fluid">
				<?php // Get the menu parameters that are automatically set but may be modified.
				echo $this->loadTemplate('options'); ?>
			</div>
		</div>

		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>

		<!-- End Content -->
	</div>

</form>
