<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Languages\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Table\Language;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Languages Model Class
 *
 * @since  1.6
 */
class LanguagesModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array                 $config   An optional associative array of configuration settings.
     * @param   ?MVCFactoryInterface  $factory  The factory.
     *
     * @see     \Joomla\CMS\MVC\Model\BaseDatabaseModel
     * @since   3.2
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'lang_id', 'a.lang_id',
                'lang_code', 'a.lang_code',
                'title', 'a.title',
                'title_native', 'a.title_native',
                'sef', 'a.sef',
                'image', 'a.image',
                'published', 'a.published',
                'ordering', 'a.ordering',
                'access', 'a.access', 'access_level',
                'home', 'l.home',
            ];
        }

        parent::__construct($config, $factory);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState($ordering = 'a.ordering', $direction = 'asc')
    {
        // Load the parameters.
        $params = ComponentHelper::getParams('com_languages');
        $this->setState('params', $params);

        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string  A store id.
     *
     * @since   1.6
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.published');

        return parent::getStoreId($id);
    }

    /**
     * Method to build an SQL query to load the list data.
     *
     * @return  QueryInterface    An SQL query
     *
     * @since   1.6
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Select all fields from the languages table.
        $query->select(
            $this->getState(
                'list.select',
                [
                    $db->quoteName('a') . '.*',
                ]
            )
        )
            ->select(
                [
                    $db->quoteName('l.home'),
                    $db->quoteName('ag.title', 'access_level'),
                ]
            )
            ->from($db->quoteName('#__languages', 'a'))
            ->join('LEFT', $db->quoteName('#__viewlevels', 'ag'), $db->quoteName('ag.id') . ' = ' . $db->quoteName('a.access'))
            ->join(
                'LEFT',
                $db->quoteName('#__menu', 'l'),
                $db->quoteName('l.language') . ' = ' . $db->quoteName('a.lang_code')
                    . ' AND ' . $db->quoteName('l.home') . ' = 1 AND ' . $db->quoteName('l.language') . ' <> ' . $db->quote('*')
            );

        // Filter on the published state.
        $published = (string) $this->getState('filter.published');

        if (is_numeric($published)) {
            $published = (int) $published;
            $query->where($db->quoteName('a.published') . ' = :published')
                ->bind(':published', $published, ParameterType::INTEGER);
        } elseif ($published === '') {
            $query->where($db->quoteName('a.published') . ' IN (0, 1)');
        }

        // Filter by search in title.
        if ($search = $this->getState('filter.search')) {
            $search = '%' . str_replace(' ', '%', trim($search)) . '%';
            $query->where($db->quoteName('a.title') . ' LIKE :search')
                ->bind(':search', $search);
        }

        // Filter by access level.
        if ($access = (int) $this->getState('filter.access')) {
            $query->where($db->quoteName('a.access') . ' = :access')
                ->bind(':access', $access, ParameterType::INTEGER);
        }

        // Add the list ordering clause.
        $query->order($db->quoteName($db->escape($this->getState('list.ordering', 'a.ordering')))
            . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

        return $query;
    }

    /**
     * Set the published language(s).
     *
     * @param   array    $cid    An array of language IDs.
     * @param   integer  $value  The value of the published state.
     *
     * @return  boolean  True on success, false otherwise.
     *
     * @since   1.6
     */
    public function setPublished($cid, $value = 0)
    {
        $table = new Language($this->getDatabase());

        return $table->publish($cid, $value);
    }

    /**
     * Method to delete records.
     *
     * @param   array  $pks  An array of item primary keys.
     *
     * @return  boolean  Returns true on success, false on failure.
     *
     * @since   1.6
     */
    public function delete($pks)
    {
        // Sanitize the array.
        $pks = (array) $pks;

        // Get a row instance.
        $table = new Language($this->getDatabase());

        // Iterate the items to delete each one.
        foreach ($pks as $itemId) {
            if (!$table->delete((int) $itemId)) {
                $this->setError($table->getError());

                return false;
            }
        }

        // Clean the cache.
        $this->cleanCache();

        return true;
    }

    /**
     * Custom clean cache method, 2 places for 2 clients.
     *
     * @param  string  $group  Cache group name.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function cleanCache($group = null)
    {
        parent::cleanCache('_system');
        parent::cleanCache('com_languages');
    }
}
