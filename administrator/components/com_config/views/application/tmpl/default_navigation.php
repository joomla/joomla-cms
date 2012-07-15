<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<ul class="nav nav-list">
	<li class="nav-header"><?php echo JText::_('COM_CONFIG_SYSTEM'); ?></li>
	<li class="active"><a href="index.php?option=com_config"><?php echo JText::_('COM_CONFIG_GLOBAL_CONFIGURATION'); ?></a></li>
	<li class="divider"></li>
	<li class="nav-header"><?php echo JText::_('COM_CONFIG_COMPONENT_FIELDSET_LABEL'); ?></li>
	<li><a href="index.php?option=com_config&view=component&component=com_banners&path="><?php echo JText::_('Banners'); ?></a></li>
	<li><a href="index.php?option=com_config&view=component&component=com_categories&path="><?php echo JText::_('Categories'); ?></a></li>
	<li><a href="index.php?option=com_config&view=component&component=com_contact&path="><?php echo JText::_('Contacts'); ?></a></li>
	<li><a href="index.php?option=com_config&view=component&component=com_content&path="><?php echo JText::_('Content'); ?></a></li>
	<li><a href="index.php?option=com_config&view=component&component=com_installer&path="><?php echo JText::_('Extensions'); ?></a></li>
	<li><a href="index.php?option=com_config&view=component&component=com_languages&path="><?php echo JText::_('Languages'); ?></a></li>
	<li><a href="index.php?option=com_config&view=component&component=com_media&path="><?php echo JText::_('Media'); ?></a></li>
	<li><a href="index.php?option=com_config&view=component&component=com_menus&path="><?php echo JText::_('Menus'); ?></a></li>
	<li><a href="index.php?option=com_config&view=component&component=com_messages&path="><?php echo JText::_('Messages'); ?></a></li>
	<li><a href="index.php?option=com_config&view=component&component=com_modules&path="><?php echo JText::_('Modules'); ?></a></li>
	<li><a href="index.php?option=com_config&view=component&component=com_newsfeeds&path="><?php echo JText::_('Newsfeeds'); ?></a></li>
	<li><a href="index.php?option=com_config&view=component&component=com_redirect&path="><?php echo JText::_('Redirect'); ?></a></li>
	<li><a href="index.php?option=com_config&view=component&component=com_search&path="><?php echo JText::_('Search'); ?></a></li>
	<li><a href="index.php?option=com_config&view=component&component=com_finder&path="><?php echo JText::_('Smart Search'); ?></a></li>
	<li><a href="index.php?option=com_config&view=component&component=com_templates&path="><?php echo JText::_('Templates'); ?></a></li>
	<li><a href="index.php?option=com_config&view=component&component=com_users&path="><?php echo JText::_('Users'); ?></a></li>
	<li><a href="index.php?option=com_config&view=component&component=com_weblinks&path="><?php echo JText::_('Weblinks'); ?></a></li>
	<li class="divider"></li>
	<li class="nav-header"><?php echo JText::_('Extensions'); ?></li>
</ul>