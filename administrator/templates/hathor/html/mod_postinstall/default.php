<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="alert" style="margin-bottom: 20px; line-height: 2em; color:#333333; clear:both;">
	<div class="modal-body">
		<h4>
			<?php echo JText::_('MOD_POSTINSTALL_MESSAGES_TITLE'); ?>
		</h4>
		<p>
			<?php echo JText::_('MOD_POSTINSTALL_MESSAGES_BODY'); ?>
		</p>
		<p>
			<?php echo JText::_('MOD_POSTINSTALL_MESSAGES_BODYMORE'); ?>
		</p>
	</div>
	<div class="modal-footer">
		<button onclick="window.location='index.php?option=com_postinstall&eid=<?php echo $eid; ?>'; return false" class="btn btn-primary btn-large" >
			<?php echo JText::_('MOD_POSTINSTALL_MESSAGES_REVIEW'); ?>
		</button>
	</div>
</div>
