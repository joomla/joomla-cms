<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/* @var InstallationViewRemoveHtml $this */
?>
<form action="index.php" method="post" id="adminForm" class="form-validate">
	<div class="alert alert-danger inlineError" id="theDefaultError" style="display:none">
		<h4 class="alert-heading"><?php echo JText::_('JERROR'); ?></h4>
		<p id="theDefaultErrorMessage"></p>
	</div>
	<div class="alert alert-success">
	<h3><?php echo JText::_('INSTL_COMPLETE_TITLE'); ?></h3>
	</div>
	<div class="alert alert-info">
		<p><?php echo JText::_('INSTL_COMPLETE_REMOVE_INSTALLATION'); ?></p>
		<input type="button" class="btn btn-warning" name="instDefault" onclick="Install.removeFolder(this);" value="<?php echo JText::_('INSTL_COMPLETE_REMOVE_FOLDER'); ?>">
	</div>
	<div>
		<div class="btn-group">
			<a class="btn btn-secondary" href="<?php echo JUri::root(); ?>" title="<?php echo JText::_('JSITE'); ?>"><span class="fa fa-eye"></span> <?php echo JText::_('JSITE'); ?></a>
		</div>
		<div class="btn-group">
			<a class="btn btn-primary" href="<?php echo JUri::root(); ?>administrator/" title="<?php echo JText::_('JADMINISTRATOR'); ?>"><span class="fa fa-lock"></span> <?php echo JText::_('JADMINISTRATOR'); ?></a>
		</div>
	</div>
	<?php echo JHtml::_('form.token'); ?>
</form>
