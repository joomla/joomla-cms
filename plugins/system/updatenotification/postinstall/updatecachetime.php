<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.updatenotification
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Checks if the com_installer config for the cache Hours are eq 0 and the updatenotification Plugin is enabled
 *
 * @return  boolean
 *
 * @since   3.6.3
 */
function updatecachetime_postinstall_condition()
{
    $cacheTimeout = (int) ComponentHelper::getComponent('com_installer')->params->get('cachetimeout', 6);

    // Check if cachetimeout is eq zero
    if ($cacheTimeout === 0 && PluginHelper::isEnabled('system', 'updatenotification')) {
        return true;
    }

    return false;
}

/**
 * Sets the cachetimeout back to the default (6 hours)
 *
 * @return  void
 *
 * @since   3.6.3
 */
function updatecachetime_postinstall_action()
{
    $installer = ComponentHelper::getComponent('com_installer');

    // Sets the cachetimeout back to the default (6 hours)
    $installer->params->set('cachetimeout', 6);

    // Save the new parameters back to com_installer
    $table = Table::getInstance('extension');
    $table->load($installer->id);
    $table->bind(['params' => $installer->params->toString()]);

    // Store the changes
    if (!$table->store()) {
        // If there is an error show it to the admin
        Factory::getApplication()->enqueueMessage($table->getError(), 'error');
    }
}
