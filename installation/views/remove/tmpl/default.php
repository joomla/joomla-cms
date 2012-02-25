<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div id="step">
	<div class="buttons">
		<div class="button"><a href="<?php echo JURI::root(); ?>" class="site" title="<?php echo JText::_('JSITE'); ?>"><?php echo JText::_('JSITE'); ?></a></div>
		<div class="button"><a href="<?php echo JURI::root(); ?>administrator/" class="admin" title="<?php echo JText::_('JADMINISTRATOR'); ?>"><?php echo JText::_('JADMINISTRATOR'); ?></a></div>
	</div>
	<h2><?php echo JText::_('INSTL_COMPLETE_REMOVE_FOLDER'); ?></h2>
</div>

<div id="installer">
	<p class="error remove"><?php echo JText::_('INSTL_COMPLETE_REMOVE_INSTALLATION'); ?></p>
</div>
