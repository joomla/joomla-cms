<?php

/**
 * @copyright     Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;

require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/profiles.php';

class JceModelProfiles extends JModelList
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see     JControllerLegacy
     * @since   1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'id',
                'name', 'name',
                'checked_out', 'checked_out',
                'checked_out_time', 'checked_out_time',
                'published', 'published',
                'ordering', 'ordering',
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @note    Calling getState in this method will result in recursion.
     * @since   1.6
     */
    protected function populateState($ordering = null, $direction = null)
    {
        // Load the filter state.
        $this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));

        // Load the parameters.
        $params = JComponentHelper::getParams('com_jce');
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

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  JDatabaseQuery
     *
     * @since   1.6
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $user = JFactory::getUser();

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'id, name, description, ordering, published, checked_out, checked_out_time'
            )
        );

        $query->from($db->quoteName('#__wf_profiles'));
        $query->where('(' . $db->quoteName('published') . ' IN (0, 1))');

        // Filter by search in title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where($db->quoteName('id') . ' = ' . (int) substr($search, 3));
            } else {
                $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
                $query->where('(' . $db->quoteName('name') . ' LIKE ' . $search . ' OR ' . $db->quoteName('description') . ' LIKE ' . $search . ')');
            }
        }

        // Add the list ordering clause.
        $listOrder = $this->getState('list.ordering', 'ordering');
        $listDirn = $this->getState('list.direction', 'ASC');

        $query->order($db->escape($listOrder . ' ' . $listDirn));

        return $query;
    }

    public function repair()
    {
		$file = __DIR__ . '/profiles.xml';

        if (!is_file($file)) {
            $this->setError(JText::_('WF_PROFILES_REPAIR_ERROR'));
            return false;
        }

        $xml = simplexml_load_file($file);

        if (!$xml) {
            $this->setError(JText::_('WF_PROFILES_REPAIR_ERROR'));
            return false;
        }

        foreach ($xml->profiles->children() as $profile) {
			$groups = JceProfilesHelper::getUserGroups((int) $profile->children('area'));

			$table = JTable::getInstance('Profiles', 'JceTable');

            foreach ($profile->children() as $item) {
                switch ((string) $item->getName()) {
					case 'description':
                        $table->description = JText::_((string) $item);
                    case 'types':
                        $table->types = implode(',', $groups);
                        break;
                    case 'area':
                        $table->area = (int) $item;
                        break;
                    case 'rows':
                        $table->rows = (string) $item;
                        break;
                    case 'plugins':
                        $table->plugins = (string) $item;
                        break;
                    default:
                        $key = $item->getName();
                        $table->$key = (string) $item;

                        break;
                }
            }

            // Check the data.
            if (!$table->check()) {
                $this->setError($table->getError());

                return false;
            }

            // Store the data.
            if (!$table->store()) {
                $this->setError($table->getError());

                return false;
            }
		}
		
		return true;
    }
}
