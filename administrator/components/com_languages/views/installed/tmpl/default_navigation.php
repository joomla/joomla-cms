<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$client	= (int) $this->state->get('filter.client_id', 0);
?>
<ul id="submenu" class="languages nav nav-list">
	<li class="<?php echo ($client == 0) ? 'active' : '';?>"><a href="index.php?option=com_languages&view=installed&client=0">
	<?php echo JText::_('COM_LANGUAGES_SUBMENU_INSTALLED_SITE'); ?></a></li>
	<li class="<?php echo ($client == 1) ? 'active' : '';?>"><a href="index.php?option=com_languages&view=installed&client=1">
	<?php echo JText::_('COM_LANGUAGES_SUBMENU_INSTALLED_ADMINISTRATOR'); ?></a></li>
	<li><a href="index.php?option=com_languages&view=languages">
	<?php echo JText::_('COM_LANGUAGES_SUBMENU_CONTENT'); ?></a></li>
	<li><a href="index.php?option=com_languages&view=overrides">
	<?php echo JText::_('COM_LANGUAGES_SUBMENU_OVERRIDES'); ?></a></li>
</ul>
