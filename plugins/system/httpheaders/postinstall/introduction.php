<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.HttpHeaders
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;

/**
 * Checks if the plugin is enabled. If not it returns true, meaning that the
 * message concerning the HTTPHeaders Plugin should be displayed.
 *
 * @return  integer
 *
 * @since   4.0.0
 */
function httpheaders_postinstall_condition()
{
    return !Joomla\CMS\Plugin\PluginHelper::isEnabled('system', 'httpheaders');
}

/**
 * Enables the HTTPHeaders plugin
 *
 * @return  void
 *
 * @since   4.0.0
 */
function httpheaders_postinstall_action()
{
    // Enable the plugin
    $db = Factory::getDbo();

    $query = $db->getQuery(true)
        ->update($db->quoteName('#__extensions'))
        ->set($db->quoteName('enabled') . ' = 1')
        ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
        ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
        ->where($db->quoteName('element') . ' = ' . $db->quote('httpheaders'));
    $db->setQuery($query);
    $db->execute();

    $query = $db->getQuery(true)
        ->select('extension_id')
        ->from($db->quoteName('#__extensions'))
        ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
        ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
        ->where($db->quoteName('element') . ' = ' . $db->quote('httpheaders'));
    $db->setQuery($query);
    $extensionId = $db->loadResult();

    $url = 'index.php?option=com_plugins&task=plugin.edit&extension_id=' . $extensionId;
    Factory::getApplication()->redirect($url);
}
