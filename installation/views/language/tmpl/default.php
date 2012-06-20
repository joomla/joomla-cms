<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<form action="index.php" method="post" id="adminForm" class="form-validate form-horizontal">
	<div id="installer">
		<div class="btn-toolbar">
			<div class="btn-group">
				<a class="btn" data-toggle="modal" href="#licenseModal"><i class="icon-eye-open"></i> <?php echo JText::_('INSTL_LICENSE'); ?></a>
			</div>
			<div class="btn-group">
				<a href="#" class="btn btn-primary" onclick="Install.submitform();" rel="next" title="<?php echo JText::_('JNext'); ?>"><i class="icon-arrow-right icon-white"></i> <?php echo JText::_('JNext'); ?></a>
			</div>
		</div>
		<h3><?php echo JText::_('INSTL_LANGUAGE_TITLE'); ?></h3>
		<div class="control-group">
			<label for="jform_language" class="control-label"><?php echo JText::_('INSTL_SELECT_LANGUAGE_TITLE'); ?></label>
			<div class="controls">
				<?php echo $this->form->getInput('language'); ?>
			</div>
		</div>
	</div>
	<input type="hidden" name="task" value="setup.setlanguage" />
	<?php echo JHtml::_('form.token'); ?>
</form>

<div id="licenseModal" class="modal fade">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">×</button>
	    <h3><?php echo JText::_('INSTL_GNU_GPL_LICENSE'); ?></h3>
	</div>
	<div class="modal-body">
		<iframe src="gpl.html" class="thumbnail span6 license" height="250" marginwidth="25" scrolling="auto"></iframe>
	</div>
</div>
