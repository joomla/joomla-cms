<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

?>
<a class="btn btn-secondary" data-dismiss="modal" type="button" onclick="document.getElementById('batch_urls').value='';">
	<?php echo JText::_('JCANCEL'); ?>
</a>
<button class="btn btn-success" type="submit" onclick="Joomla.submitbutton('links.batch');">
	<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
</button>
