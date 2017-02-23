<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$text = JText::_('COM_MESSAGES_TOOLBAR_MY_SETTINGS');
?>
<a
	rel="{handler:'iframe', size:{x:700,y:300}}"
	href="index.php?option=com_messages&amp;view=config&amp;tmpl=component"
	title="<?php echo $text; ?>" class="messagesSettings btn btn-outline-primary btn-sm">
		<span class="icon-cog"></span> <?php echo $text; ?>
</a>
