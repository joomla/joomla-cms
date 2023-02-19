<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Plugins\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Object\CMSObject;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Plugins component helper.
 *
 * @since  1.6
 */
class PluginsHelper
{
    public static $extension = 'com_plugins';

    /**
     * Returns an array of standard published state filter options.
     *
     * @return  array    The HTML code for the select tag
     */
    public static function publishedOptions()
    {
        // Build the active state filter options.
        $options = [];
        $options[] = HTMLHelper::_('select.option', '1', 'JENABLED');
        $options[] = HTMLHelper::_('select.option', '0', 'JDISABLED');

        return $options;
    }

    /**
     * Returns a list of folders filter options.
     *
     * @return  string    The HTML code for the select tag
     */
    public static function folderOptions()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('DISTINCT(folder) AS value, folder AS text')
            ->from('#__extensions')
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->order('folder');

        $db->setQuery($query);

        try {
            $options = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $options;
    }

    /**
     * Returns a list of elements filter options.
     *
     * @return  string    The HTML code for the select tag
     */
    public static function elementOptions()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('DISTINCT(element) AS value, element AS text')
            ->from('#__extensions')
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->order('element');
        $db->setQuery($query);

        try {
            $options = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $options;
    }

    /**
     * Parse the template file.
     *
     * @param   string  $templateBaseDir  Base path to the template directory.
     * @param   string  $templateDir      Template directory.
     *
     * @return  CMSObject|bool
     */
    public function parseXMLTemplateFile($templateBaseDir, $templateDir)
    {
        $data = new CMSObject();

        // Check of the xml file exists.
        $filePath = Path::clean($templateBaseDir . '/templates/' . $templateDir . '/templateDetails.xml');

        if (is_file($filePath)) {
            $xml = Installer::parseXMLInstallFile($filePath);

            if ($xml['type'] != 'template') {
                return false;
            }

            foreach ($xml as $key => $value) {
                $data->set($key, $value);
            }
        }

        return $data;
    }
}
