<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Helper;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use SimpleXMLElement;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Installer helper.
 *
 * @since  1.6
 */
class InstallerHelper
{
    /**
     * Get a list of filter options for the extension types.
     *
     * @return  array  An array of \stdClass objects.
     *
     * @since   3.0
     */
    public static function getExtensionTypes()
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('type'))
            ->from($db->quoteName('#__extensions'));
        $db->setQuery($query);
        $types = $db->loadColumn();

        $options = [];

        foreach ($types as $type) {
            $options[] = HTMLHelper::_('select.option', $type, Text::_('COM_INSTALLER_TYPE_' . strtoupper($type)));
        }

        return $options;
    }

    /**
     * Get a list of filter options for the extension types.
     *
     * @return  array  An array of \stdClass objects.
     *
     * @since   3.0
     */
    public static function getExtensionGroups()
    {
        $nofolder = '';
        $db       = Factory::getDbo();
        $query    = $db->getQuery(true)
            ->select('DISTINCT ' . $db->quoteName('folder'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('folder') . ' != :folder')
            ->bind(':folder', $nofolder)
            ->order($db->quoteName('folder'));
        $db->setQuery($query);
        $folders = $db->loadColumn();

        $options = [];

        foreach ($folders as $folder) {
            $options[] = HTMLHelper::_('select.option', $folder, $folder);
        }

        return $options;
    }

    /**
     * Get a list of filter options for the application clients.
     *
     * @return  array  An array of \JHtmlOption elements.
     *
     * @since   3.5
     */
    public static function getClientOptions()
    {
        // Build the filter options.
        $options   = [];
        $options[] = HTMLHelper::_('select.option', '0', Text::_('JSITE'));
        $options[] = HTMLHelper::_('select.option', '1', Text::_('JADMINISTRATOR'));
        $options[] = HTMLHelper::_('select.option', '3', Text::_('JAPI'));

        return $options;
    }

    /**
     * Get a list of filter options for the application statuses.
     *
     * @return  array  An array of \JHtmlOption elements.
     *
     * @since   3.5
     */
    public static function getStateOptions()
    {
        // Build the filter options.
        $options   = [];
        $options[] = HTMLHelper::_('select.option', '0', Text::_('JDISABLED'));
        $options[] = HTMLHelper::_('select.option', '1', Text::_('JENABLED'));
        $options[] = HTMLHelper::_('select.option', '2', Text::_('JPROTECTED'));
        $options[] = HTMLHelper::_('select.option', '3', Text::_('JUNPROTECTED'));

        return $options;
    }

    /**
     * Get a list of filter options for extensions of the "package" type.
     *
     * @return  array
     * @since   4.2.0
     */
    public static function getPackageOptions(): array
    {
        $options = [];

        /** @var DatabaseDriver $db The application's database driver object */
        $db         = Factory::getContainer()->get(DatabaseDriver::class);
        $query      = $db->getQuery(true)
            ->select(
                $db->quoteName(
                    [
                        'extension_id',
                        'name',
                        'element',
                    ]
                )
            )
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = ' . $db->quote('package'));
        $extensions = $db->setQuery($query)->loadObjectList() ?: [];

        if (empty($extensions)) {
            return $options;
        }

        $language  = Factory::getApplication()->getLanguage();
        $arrayKeys = array_map(
            function (object $entry) use ($language): string {
                $language->load($entry->element, JPATH_ADMINISTRATOR);

                return Text::_($entry->name);
            },
            $extensions
        );
        $arrayValues = array_map(
            function (object $entry): int {
                return $entry->extension_id;
            },
            $extensions
        );

        $extensions = array_combine($arrayKeys, $arrayValues);
        ksort($extensions);

        foreach ($extensions as $label => $id) {
            $options[] = HTMLHelper::_('select.option', $id, $label);
        }

        return $options;
    }

    /**
     * Get a list of filter options for the application statuses.
     *
     * @param   string   $element   element of an extension
     * @param   string   $type      type of an extension
     * @param   integer  $clientId  client_id of an extension
     * @param   string   $folder    folder of an extension
     *
     * @return  SimpleXMLElement
     *
     * @since   4.0.0
     */
    public static function getInstallationXML(
        string $element,
        string $type,
        int $clientId = 1,
        ?string $folder = null
    ): ?SimpleXMLElement {
        $path = [0 => JPATH_SITE, 1 => JPATH_ADMINISTRATOR, 3 => JPATH_API][$clientId] ?? JPATH_SITE;

        switch ($type) {
            case 'component':
                $path .= '/components/' . $element . '/' . substr($element, 4) . '.xml';
                break;
            case 'plugin':
                $path .= '/plugins/' . $folder . '/' . $element . '/' . $element . '.xml';
                break;
            case 'module':
                $path .= '/modules/' . $element . '/' . $element . '.xml';
                break;
            case 'template':
                $path .= '/templates/' . $element . '/templateDetails.xml';
                break;
            case 'library':
                $path = JPATH_ADMINISTRATOR . '/manifests/libraries/' . $element . '.xml';
                break;
            case 'file':
                $path = JPATH_ADMINISTRATOR . '/manifests/files/' . $element . '.xml';
                break;
            case 'package':
                $path = JPATH_ADMINISTRATOR . '/manifests/packages/' . $element . '.xml';
                break;
            case 'language':
                $path .= '/language/' . $element . '/install.xml';
        }

        if (file_exists($path) === false) {
            return null;
        }

        $xmlElement = simplexml_load_file($path);

        return ($xmlElement !== false) ? $xmlElement : null;
    }

    /**
     * Get the download key of an extension going through their installation xml
     *
     * @param   CMSObject  $extension  element of an extension
     *
     * @return  array  An array with the prefix, suffix and value of the download key
     *
     * @since   4.0.0
     */
    public static function getDownloadKey(CMSObject $extension): array
    {
        $installXmlFile = self::getInstallationXML(
            $extension->get('element'),
            $extension->get('type'),
            $extension->get('client_id'),
            $extension->get('folder')
        );

        if (!$installXmlFile) {
            return [
                'supported' => false,
                'valid'     => false,
            ];
        }

        if (!isset($installXmlFile->dlid)) {
            return [
                'supported' => false,
                'valid'     => false,
            ];
        }

        $prefix = (string) $installXmlFile->dlid['prefix'];
        $suffix = (string) $installXmlFile->dlid['suffix'];
        $value  = substr($extension->get('extra_query'), strlen($prefix));

        if ($suffix) {
            $value = substr($value, 0, -strlen($suffix));
        }

        $downloadKey = [
            'supported' => true,
            'valid'     => $value ? true : false,
            'prefix'    => $prefix,
            'suffix'    => $suffix,
            'value'     => $value
        ];

        return $downloadKey;
    }

    /**
     * Get the download key of an extension given enough information to locate it in the #__extensions table
     *
     * @param   string       $element   Name of the extension, e.g. com_foo
     * @param   string       $type      The type of the extension, e.g. component
     * @param   int          $clientId  [optional] Joomla client for the extension, see the #__extensions table
     * @param   string|null  $folder    Extension folder, only applies for 'plugin' type
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public static function getExtensionDownloadKey(
        string $element,
        string $type,
        int $clientId = 1,
        ?string $folder = null
    ): array {
        // Get the database driver. If it fails we cannot report whether the extension supports download keys.
        try {
            $db = Factory::getDbo();
        } catch (Exception $e) {
            return [
                'supported' => false,
                'valid'     => false,
            ];
        }

        // Try to retrieve the extension information as a CMSObject
        $query = $db->getQuery(true)
            ->select($db->quoteName('extension_id'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('type') . ' = :type')
            ->where($db->quoteName('element') . ' = :element')
            ->where($db->quoteName('folder') . ' = :folder')
            ->where($db->quoteName('client_id') . ' = :client_id');
        $query->bind(':type', $type, ParameterType::STRING);
        $query->bind(':element', $element, ParameterType::STRING);
        $query->bind(':client_id', $clientId, ParameterType::INTEGER);
        $query->bind(':folder', $folder, ParameterType::STRING);

        try {
            $extension = new CMSObject($db->setQuery($query)->loadAssoc());
        } catch (Exception $e) {
            return [
                'supported' => false,
                'valid'     => false,
            ];
        }

        // Use the getDownloadKey() method to return the download key information
        return self::getDownloadKey($extension);
    }

    /**
     * Returns a list of update site IDs which support download keys. By default this returns all qualifying update
     * sites, even if they are not enabled.
     *
     *
     * @param   bool  $onlyEnabled  [optional] Set true to only returned enabled update sites.
     *
     * @return  int[]
     * @since   4.0.0
     */
    public static function getDownloadKeySupportedSites($onlyEnabled = false): array
    {
        /**
         * NOTE: The closures are not inlined because in this case the Joomla Code Style standard produces two mutually
         * exclusive errors, making the file impossible to commit. Using closures in variables makes the code less
         * readable but works around that issue.
         */

        $extensions = self::getUpdateSitesInformation($onlyEnabled);

        $filterClosure = function (CMSObject $extension) {
            $dlidInfo = self::getDownloadKey($extension);

            return $dlidInfo['supported'];
        };
        $extensions = array_filter($extensions, $filterClosure);

        $mapClosure = function (CMSObject $extension) {
            return $extension->get('update_site_id');
        };

        return array_map($mapClosure, $extensions);
    }

    /**
     * Returns a list of update site IDs which are missing download keys. By default this returns all qualifying update
     * sites, even if they are not enabled.
     *
     * @param   bool  $exists       [optional] If true, returns update sites with a valid download key. When false,
     *                              returns update sites with an invalid / missing download key.
     * @param   bool  $onlyEnabled  [optional] Set true to only returned enabled update sites.
     *
     * @return  int[]
     * @since   4.0.0
     */
    public static function getDownloadKeyExistsSites(bool $exists = true, $onlyEnabled = false): array
    {
        /**
         * NOTE: The closures are not inlined because in this case the Joomla Code Style standard produces two mutually
         * exclusive errors, making the file impossible to commit. Using closures in variables makes the code less
         * readable but works around that issue.
         */

        $extensions = self::getUpdateSitesInformation($onlyEnabled);

        // Filter the extensions by what supports Download Keys
        $filterClosure = function (CMSObject $extension) use ($exists) {
            $dlidInfo = self::getDownloadKey($extension);

            if (!$dlidInfo['supported']) {
                return false;
            }

            return $exists ? $dlidInfo['valid'] : !$dlidInfo['valid'];
        };
        $extensions = array_filter($extensions, $filterClosure);

        // Return only the update site IDs
        $mapClosure = function (CMSObject $extension) {
            return $extension->get('update_site_id');
        };

        return array_map($mapClosure, $extensions);
    }


    /**
     * Get information about the update sites
     *
     * @param   bool  $onlyEnabled  Only return enabled update sites
     *
     * @return  CMSObject[]  List of update site and linked extension information
     * @since   4.0.0
     */
    protected static function getUpdateSitesInformation(bool $onlyEnabled): array
    {
        try {
            $db = Factory::getDbo();
        } catch (Exception $e) {
            return [];
        }

        $query = $db->getQuery(true)
            ->select(
                $db->quoteName(
                    [
                                's.update_site_id',
                                's.enabled',
                                's.extra_query',
                                'e.extension_id',
                                'e.type',
                                'e.element',
                                'e.folder',
                                'e.client_id',
                                'e.manifest_cache',
                            ],
                    [
                                'update_site_id',
                                'enabled',
                                'extra_query',
                                'extension_id',
                                'type',
                                'element',
                                'folder',
                                'client_id',
                                'manifest_cache',
                            ]
                )
            )
            ->from($db->quoteName('#__update_sites', 's'))
            ->innerJoin(
                $db->quoteName('#__update_sites_extensions', 'se'),
                $db->quoteName('se.update_site_id') . ' = ' . $db->quoteName('s.update_site_id')
            )
            ->innerJoin(
                $db->quoteName('#__extensions', 'e'),
                $db->quoteName('e.extension_id') . ' = ' . $db->quoteName('se.extension_id')
            )
            ->where($db->quoteName('state') . ' = 0');

        if ($onlyEnabled) {
            $enabled = 1;
            $query->where($db->quoteName('s.enabled') . ' = :enabled')
                ->bind(':enabled', $enabled, ParameterType::INTEGER);
        }

        // Try to get all of the update sites, including related extension information
        try {
            $items = [];
            $db->setQuery($query);

            foreach ($db->getIterator() as $item) {
                $items[] = new CMSObject($item);
            }

            return $items;
        } catch (Exception $e) {
            return [];
        }
    }
}
