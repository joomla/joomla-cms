<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_postinstall
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="alert alert-info">
	<h4>
		<?php echo JText::_('MOD_POSTINSTALL_MESSAGES_TITLE'); ?>
	</h4>
	<p>
		<?php echo JText::_('MOD_POSTINSTALL_MESSAGES_BODY'); ?>
	</p>
	<p>
		<?php echo JText::_('MOD_POSTINSTALL_MESSAGES_BODYMORE'); ?>
	</p>
	<p>
		<a href="index.php?option=com_postinstall&eid=<?php echo $eid; ?>" class="btn btn-primary btn-large">
			<?php echo JText::_('MOD_POSTINSTALL_MESSAGES_REVIEW'); ?>
		</a>
	</p>
</div>
