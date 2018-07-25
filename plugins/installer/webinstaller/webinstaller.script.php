<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Installer.webinstaller
 *
 * @copyright   Copyright (C) 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Support for the "Install from Web" tab
 *
 * @package     Joomla.Plugin
 * @subpackage  System.webinstaller
 * @since       3.2
 */
class plginstallerwebinstallerInstallerScript
{
        public function postflight($route, $adapter)
        {
            $db = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->update($db->quoteName('#__extensions'))
                ->set($db->quoteName('enabled') . ' = 1')
                ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
                ->where($db->quoteName('element') . ' = ' . $db->quote('webinstaller'));
            $db->setQuery($query);
            $db->execute();
        }
}
