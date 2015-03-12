<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
$model = $this->getModel();
?>
<ul class="nav nav-list">
	<?php if ($this->userIsSuperAdmin): ?>
		<li class="nav-header"><?php echo JText::_('COM_CONFIG_SYSTEM'); ?></li>
		<li><a href="index.php?option=com_config"><?php echo JText::_('COM_CONFIG_GLOBAL_CONFIGURATION'); ?></a></li>
		<li class="divider"></li>
	<?php endif; ?>
	<li class="nav-header"><?php echo JText::_('COM_CONFIG_COMPONENT_FIELDSET_LABEL'); ?></li>
	<?php foreach ($this->components as $component) : ?>
		<?php if (!$model->allowAction('core.admin')): ?>
			<?php continue;?>
		<?php endif; ?>

		<?php $active = ''; ?>
		<?php if ($this->config['component'] === $component->element):?>
			<?php $active = ' class="active"';?>
		<?php endif; ?>

		<li<?php echo $active; ?>>
			<a href="index.php?option=com_config&view=component&component=<?php echo $component->element; ?>&id=<?php echo $component->extension_id;?>"><?php echo JText::_($component->name); ?></a>
		</li>
	<?php endforeach; ?>
</ul>
