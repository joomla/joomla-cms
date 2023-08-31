<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/** @var \Joomla\Component\Admin\Administrator\View\Sysinfo\HtmlView $this */

?>
<div class="sysinfo">
    <table class="table">
        <caption class="visually-hidden">
            <?php echo Text::_('COM_ADMIN_SYSTEM_INFORMATION'); ?>
        </caption>
        <thead>
            <tr>
                <th scope="col" class="w-30">
                    <?php echo Text::_('COM_ADMIN_SETTING'); ?>
                </th>
                <th scope="col">
                    <?php echo Text::_('COM_ADMIN_VALUE'); ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th scope="row">
                    <?php echo Text::_('COM_ADMIN_PHP_BUILT_ON'); ?>
                </th>
                <td>
                    <?php echo $this->info['php']; ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php echo Text::_('COM_ADMIN_DATABASE_TYPE'); ?>
                </th>
                <td>
                    <?php echo $this->info['dbserver']; ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php echo Text::_('COM_ADMIN_DATABASE_VERSION'); ?>
                </th>
                <td>
                    <?php echo $this->info['dbversion']; ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php echo Text::_('COM_ADMIN_DATABASE_COLLATION'); ?>
                </th>
                <td>
                    <?php echo $this->info['dbcollation']; ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php echo Text::_('COM_ADMIN_DATABASE_CONNECTION_COLLATION'); ?>
                </th>
                <td>
                    <?php echo $this->info['dbconnectioncollation']; ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php echo Text::_('COM_ADMIN_DATABASE_CONNECTION_ENCRYPTION'); ?>
                </th>
                <td>
                    <?php echo $this->info['dbconnectionencryption'] ?: Text::_('JNONE'); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php echo Text::_('COM_ADMIN_DATABASE_CONNECTION_ENCRYPTION_SUPPORTED'); ?>
                </th>
                <td>
                    <?php echo $this->info['dbconnencryptsupported'] ? Text::_('JYES') : Text::_('JNO'); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php echo Text::_('COM_ADMIN_PHP_VERSION'); ?>
                </th>
                <td>
                    <?php echo $this->info['phpversion']; ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php echo Text::_('COM_ADMIN_WEB_SERVER'); ?>
                </th>
                <td>
                    <?php echo HTMLHelper::_('system.server', $this->info['server']); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php echo Text::_('COM_ADMIN_WEBSERVER_TO_PHP_INTERFACE'); ?>
                </th>
                <td>
                    <?php echo $this->info['sapi_name']; ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php echo Text::_('COM_ADMIN_JOOMLA_VERSION'); ?>
                </th>
                <td>
                    <?php echo $this->info['version']; ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php echo Text::_('COM_ADMIN_JOOMLA_COMPAT_PLUGIN'); ?>
                </th>
                <td>
                    <?php echo $this->info['compatpluginenabled'] ? Text::_('JENABLED') . ' (' . $this->info['compatpluginparameters'] . ')' : Text::_('JDISABLED'); ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <?php echo Text::_('COM_ADMIN_USER_AGENT'); ?>
                </th>
                <td>
                    <?php echo htmlspecialchars($this->info['useragent'], ENT_COMPAT, 'UTF-8'); ?>
                </td>
            </tr>
        </tbody>
    </table>
</div>
