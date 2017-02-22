<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Load tooltips behavior
JHtml::_('behavior.formvalidator');
JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', 'select');
?>

<form action="<?php echo JRoute::_('index.php?option=com_config');?>" id="application-form" method="post" name="adminForm" class="form-validate">

	<div class="row-fluid">
		<!-- Begin Content -->

		<div class="btn-toolbar">
			<div class="btn-group">
				<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('config.save.config.apply')">
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
				<?php echo $this->loadTemplate('site'); ?>
				<?php echo $this->loadTemplate('metadata'); ?>
				<?php echo $this->loadTemplate('seo'); ?>
			</div>
		</div>

		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>

		<!-- End Content -->
	</div>

</form>
