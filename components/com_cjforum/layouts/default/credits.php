<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

$app 				= JFactory::getApplication();
$user 				= JFactory::getUser();
$params 			= $displayData['params'];

if($params->get('show_credits_block', 1) == 1)
{
	?>
	<div class="text-center">
		<small class="text-muted">
			<?php echo JText::_('COM_CJFORUM_CREDITS_TEXT');?> <a href="https://www.corejoomla.com/products/cjforum.html" target="_blank">CjForum</a>
		</small>
	</div>
	<?php
}