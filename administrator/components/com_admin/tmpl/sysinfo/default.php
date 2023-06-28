<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

/** @var \Joomla\Component\Admin\Administrator\View\Sysinfo\HtmlView $this */

?>
<div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'site', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'site', $this->text('COM_ADMIN_SYSTEM_INFORMATION')); ?>
        <?php echo $this->loadTemplate('system'); ?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'phpsettings', $this->text('COM_ADMIN_PHP_SETTINGS')); ?>
        <?php echo $this->loadTemplate('phpsettings'); ?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'config', $this->text('COM_ADMIN_CONFIGURATION_FILE')); ?>
        <?php echo $this->loadTemplate('config'); ?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'directory', $this->text('COM_ADMIN_DIRECTORY_PERMISSIONS')); ?>
        <?php echo $this->loadTemplate('directory'); ?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'phpinfo', $this->text('COM_ADMIN_PHP_INFORMATION')); ?>
        <?php echo $this->loadTemplate('phpinfo'); ?>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
</div>
