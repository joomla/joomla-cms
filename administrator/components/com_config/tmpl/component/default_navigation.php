<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;
?>
<ul class="nav flex-column">
    <?php if ($this->userIsSuperAdmin) : ?>
        <li class="nav-header"><?php echo Text::_('COM_CONFIG_SYSTEM'); ?></li>
        <li class="item"><a href="index.php?option=com_config"><?php echo Text::_('COM_CONFIG_GLOBAL_CONFIGURATION'); ?></a></li>
        <li class="divider"></li>
    <?php endif; ?>
    <li class="nav-header"><?php echo Text::_('COM_CONFIG_COMPONENT_FIELDSET_LABEL'); ?></li>
    <?php foreach ($this->components as $component) : ?>
        <?php
        $active = '';
        if ($this->currentComponent === $component) {
            $active = ' active';
        }
        ?>
        <li class="item<?php echo $active; ?>">
            <a href="index.php?option=com_config&view=component&component=<?php echo $component; ?>"><?php echo Text::_($component); ?></a>
        </li>
    <?php endforeach; ?>
</ul>
