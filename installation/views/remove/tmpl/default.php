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
<fieldset>
	<legend><?php echo JText::_('INSTL_COMPLETE_REMOVE_FOLDER'); ?></legend>
	<div class="control-group">
		<label for="" class="control-label">

		</label>
		<div class="controls" id="installer">
			<div class="alert alert-info">
				<?php echo JText::_('INSTL_COMPLETE_REMOVE_INSTALLATION'); ?>
			</div>
		</div>
	</div>
	<div class="control-group">
		<label for="" class="control-label">

		</label>
		<div class="controls">
			<a class="btn" href="<?php echo JURI::root(); ?>" title="<?php echo JText::_('JSITE'); ?>"><i class="icon-eye-open"></i> <?php echo JText::_('JSITE'); ?></a>
			<a class="btn btn-primary" href="<?php echo JURI::root(); ?>administrator/" title="<?php echo JText::_('JADMINISTRATOR'); ?>"><i class="icon-lock icon-white"></i> <?php echo JText::_('JADMINISTRATOR'); ?></a>
		</div>
	</div>
</fieldset>
