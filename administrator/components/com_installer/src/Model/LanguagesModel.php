<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Languages Installer Model
 *
 * @since  2.5.7
 */
class LanguagesModel extends ListModel
{
    /**
     * Language count
     *
     * @var     integer
     * @since   3.7.0
     */
    private $languageCount;

    /**
     * Constructor.
     *
     * @param   array                 $config   An optional associative array of configuration settings.
     * @param   ?MVCFactoryInterface  $factory  The factory.
     *
     * @see     \Joomla\CMS\MVC\Model\ListModel
     * @since   1.6
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'name',
                'element',
            ];
        }

        parent::__construct($config, $factory);
    }

    /**
     * Get the Update Site
     *
     * @since   3.7.0
     *
     * @return  string  The URL of the Accredited Languagepack Updatesite XML
     */
    private function getUpdateSite()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('us.location'))
            ->from($db->quoteName('#__extensions', 'e'))
            ->where($db->quoteName('e.type') . ' = ' . $db->quote('package'))
            ->where($db->quoteName('e.element') . ' = ' . $db->quote('pkg_en-GB'))
            ->where($db->quoteName('e.client_id') . ' = 0')
            ->join(
                'LEFT',
                $db->quoteName('#__update_sites_extensions', 'use')
                . ' ON ' . $db->quoteName('use.extension_id') . ' = ' . $db->quoteName('e.extension_id')
            )
            ->join(
                'LEFT',
                $db->quoteName('#__update_sites', 'us')
                . ' ON ' . $db->quoteName('us.update_site_id') . ' = ' . $db->quoteName('use.update_site_id')
            );

        return $db->setQuery($query)->loadResult();
    }

    /**
     * Method to get an array of data items.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   3.7.0
     */
    public function getItems()
    {
        // Get a storage key.
        $store = $this->getStoreId();

        // Try to load the data from internal storage.
        if (isset($this->cache[$store])) {
            return $this->cache[$store];
        }

        try {
            // Load the list items and add the items to the internal cache.
            $this->cache[$store] = $this->getLanguages();
        } catch (\RuntimeException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        return $this->cache[$store];
    }

    /**
     * Gets an array of objects from the updatesite.
     *
     * @return  object[]  An array of results.
     *
     * @since   3.0
     * @throws  \RuntimeException
     */
    protected function getLanguages()
    {
        $updateSite = $this->getUpdateSite();

        // Check whether the updateserver is found
        if (empty($updateSite)) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_INSTALLER_MSG_WARNING_NO_LANGUAGES_UPDATESERVER'), 'warning');

            return;
        }

        try {
            $response = HttpFactory::getHttp()->get($updateSite);
        } catch (\RuntimeException) {
            $response = null;
        }

        if ($response === null || $response->code !== 200) {
            Factory::getApplication()->enqueueMessage(Text::sprintf('COM_INSTALLER_MSG_ERROR_CANT_CONNECT_TO_UPDATESERVER', $updateSite), 'error');

            return;
        }

        $updateSiteXML = simplexml_load_string($response->body);

        if (!$updateSiteXML) {
            Factory::getApplication()->enqueueMessage(Text::sprintf('COM_INSTALLER_MSG_ERROR_CANT_RETRIEVE_XML', $updateSite), 'error');

            return;
        }

        $languages     = [];
        $search        = strtolower($this->getState('filter.search', ''));

        foreach ($updateSiteXML->extension as $extension) {
            $language = new \stdClass();

            foreach ($extension->attributes() as $key => $value) {
                $language->$key = (string) $value;
            }

            if ($search) {
                if (
                    !str_contains(strtolower($language->name), $search)
                    && !str_contains(strtolower($language->element), $search)
                ) {
                    continue;
                }
            }

            $languages[$language->name] = $language;
        }

        // Workaround for php 5.3
        $that = $this;

        // Sort the array by value of subarray
        usort(
            $languages,
            function ($a, $b) use ($that) {
                $ordering = $that->getState('list.ordering', 'name');

                if (strtolower($that->getState('list.direction', 'asc')) === 'asc') {
                    return StringHelper::strcmp($a->$ordering, $b->$ordering);
                }

                return StringHelper::strcmp($b->$ordering, $a->$ordering);
            }
        );

        // Count the non-paginated list
        $this->languageCount = \count($languages);
        $limit               = ($this->getState('list.limit', 20) > 0) ? $this->getState('list.limit', 20) : $this->languageCount;

        return \array_slice($languages, $this->getStart() ?? 0, $limit);
    }

    /**
     * Returns a record count for the updatesite.
     *
     * @param   \Joomla\Database\DatabaseQuery|string  $query  The query.
     *
     * @return  integer  Number of rows for query.
     *
     * @since   3.7.0
     */
    protected function _getListCount($query)
    {
        return $this->languageCount;
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string  A store id.
     *
     * @since   2.5.7
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');

        return parent::getStoreId($id);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   list order
     * @param   string  $direction  direction in the list
     *
     * @return  void
     *
     * @since   2.5.7
     */
    protected function populateState($ordering = 'name', $direction = 'asc')
    {
        $this->setState('extension_message', Factory::getApplication()->getUserState('com_installer.extension_message'));

        parent::populateState($ordering, $direction);
    }

    /**
     * Method to compare two languages in order to sort them.
     *
     * @param   object  $lang1  The first language.
     * @param   object  $lang2  The second language.
     *
     * @return  integer
     *
     * @since   3.7.0
     */
    protected function compareLanguages($lang1, $lang2)
    {
        return strcmp($lang1->name, $lang2->name);
    }
}
