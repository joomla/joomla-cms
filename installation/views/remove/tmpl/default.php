<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="alert alert-success">
	<h3><?php echo JText::_('INSTL_COMPLETE_TITLE'); ?></h3>
</div>
<div class="alert">
	<p><?php echo JText::_('INSTL_COMPLETE_REMOVE_INSTALLATION'); ?></p>
	<button class="btn btn-warning" name="instDefault" onclick="Install.removeFolder(this);"><i class="icon-ban-circle icon-white"></i> <?php echo JText::_('INSTL_COMPLETE_REMOVE_FOLDER'); ?></button>
</div>

<div class="btn-toolbar">
	<div class="btn-group">
		<a class="btn" href="<?php echo JURI::root(); ?>" title="<?php echo JText::_('JSITE'); ?>"><i class="icon-eye-open"></i> <?php echo JText::_('JSITE'); ?></a>
	</div>
	<div class="btn-group">
		<a class="btn btn-primary" href="<?php echo JURI::root(); ?>administrator/" title="<?php echo JText::_('JADMINISTRATOR'); ?>"><i class="icon-lock icon-white"></i> <?php echo JText::_('JADMINISTRATOR'); ?></a>
	</div>
</div>
