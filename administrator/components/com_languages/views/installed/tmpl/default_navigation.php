<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
$client	= $this->state->get('filter.client_id', 0);
?>
<div id="submenu-box">
	<div class="submenu-box">
		<div class="submenu-pad">
			<ul id="submenu" class="languages">
				<li><a href="index.php?option=com_languages&view=installed&client=0" class="<?php echo ($client == "0") ? 'active' : '';?>">
				<?php echo JText::_('COM_LANGUAGES_SUBMENU_INSTALLED_SITE'); ?></a></li>
				<li><a href="index.php?option=com_languages&view=installed&client=1" class="<?php echo ($client == "1") ? 'active' : '';?>">
				<?php echo JText::_('COM_LANGUAGES_SUBMENU_INSTALLED_ADMINISTRATOR'); ?></a></li>
				<li><a href="index.php?option=com_languages&view=languages">
				<?php echo JText::_('COM_LANGUAGES_SUBMENU_CONTENT'); ?></a></li>
				<li><a href="index.php?option=com_languages&view=overrides">
				<?php echo JText::_('COM_LANGUAGES_SUBMENU_OVERRIDES'); ?></a></li>
			</ul>
			<div class="clr"></div>
		</div>
	</div>
	<div class="clr"></div>
</div>
