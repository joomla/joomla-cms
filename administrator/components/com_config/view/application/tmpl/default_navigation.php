<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<ul class="nav nav-list">
	<?php if ($this->userIsSuperAdmin): ?>
		<li class="nav-header"><?php echo JText::_('COM_CONFIG_SYSTEM'); ?></li>
		<li class="active">
			<a href="index.php?option=com_config"><?php echo JText::_('COM_CONFIG_GLOBAL_CONFIGURATION'); ?></a>
		</li>
		<li class="divider"></li>
	<?php endif; ?>
	<li class="nav-header"><?php echo JText::_('COM_CONFIG_COMPONENT_FIELDSET_LABEL'); ?></li>
	<?php foreach ($this->components as $component) : ?>
		<li>
			<a href="index.php?option=com_config&view=component&component=<?php echo $component; ?>"><?php echo JText::_($component); ?></a>
		</li>
	<?php endforeach; ?>
</ul>
