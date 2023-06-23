<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Templates\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Templates component helper.
 *
 * @since  1.6
 */
class TemplatesHelper
{
    /**
     * Get a list of filter options for the application clients.
     *
     * @return  array  An array of HtmlOption elements.
     */
    public static function getClientOptions()
    {
        // Build the filter options.
        $options   = [];
        $options[] = HTMLHelper::_('select.option', '0', Text::_('JSITE'));
        $options[] = HTMLHelper::_('select.option', '1', Text::_('JADMINISTRATOR'));

        return $options;
    }

    /**
     * Get a list of filter options for the templates with styles.
     *
     * @param   mixed  $clientId  The CMS client id (0:site | 1:administrator) or '*' for all.
     *
     * @return  array
     */
    public static function getTemplateOptions($clientId = '*')
    {
        // Build the filter options.
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select($db->quoteName('element', 'value'))
            ->select($db->quoteName('name', 'text'))
            ->select($db->quoteName('extension_id', 'e_id'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('template'))
            ->where($db->quoteName('enabled') . ' = 1')
            ->order($db->quoteName('client_id') . ' ASC')
            ->order($db->quoteName('name') . ' ASC');

        if ($clientId != '*') {
            $clientId = (int) $clientId;
            $query->where($db->quoteName('client_id') . ' = :clientid')
                ->bind(':clientid', $clientId, ParameterType::INTEGER);
        }

        $db->setQuery($query);
        $options = $db->loadObjectList();

        return $options;
    }

    /**
     * @param   string  $templateBaseDir
     * @param   string  $templateDir
     *
     * @return boolean|CMSObject
     */
    public static function parseXMLTemplateFile($templateBaseDir, $templateDir)
    {
        $data = new CMSObject();

        // Check of the xml file exists
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

    /**
     * @param   integer  $clientId
     * @param   string   $templateDir
     *
     * @return  boolean|array
     *
     * @since   3.0
     */
    public static function getPositions($clientId, $templateDir)
    {
        $positions = [];

        $templateBaseDir = $clientId ? JPATH_ADMINISTRATOR : JPATH_SITE;
        $filePath        = Path::clean($templateBaseDir . '/templates/' . $templateDir . '/templateDetails.xml');

        if (is_file($filePath)) {
            // Read the file to see if it's a valid component XML file
            $xml = simplexml_load_file($filePath);

            if (!$xml) {
                return false;
            }

            // Check for a valid XML root tag.

            // Extensions use 'extension' as the root tag.  Languages use 'metafile' instead

            if ($xml->getName() != 'extension' && $xml->getName() != 'metafile') {
                unset($xml);

                return false;
            }

            $positions = (array) $xml->positions;

            if (isset($positions['position'])) {
                $positions = (array) $positions['position'];
            } else {
                $positions = [];
            }
        }

        return $positions;
    }
}
