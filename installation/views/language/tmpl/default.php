<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<?php echo JHtml::_('installation.stepbar'); ?>
<form action="index.php" method="post" id="adminForm" class="form-validate form-horizontal">
	<div class="btn-toolbar">
		<div class="btn-group pull-right">
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
	<input type="hidden" name="task" value="setup.setlanguage" />
	<?php echo JHtml::_('form.token'); ?>
</form>
