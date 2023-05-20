<?php

/**
 * @package        JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Administrator\Model;

// No direct access.
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Database\QueryInterface;

/**
 * Methods supporting a list of Extensions records.
 *
 * @since  4.0.0
 */
class ExtensionsModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see           ListModel
     * @since         4.0.0
     * @throws  Exception
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'title', 'a.title',
                'alias', 'a.alias',
                'published', 'a.published',
                'created_by', 'a.created_by',
                'modified_by', 'a.modified_by',
                'created_on', 'a.created_on',
                'modified_on', 'a.modified_on',
                'joomla_versions', 'a.joomla_versions',
                'popular', 'a.popular',
                'requires_registration', 'a.requires_registration',
                'gpl_license_type', 'a.gpl_license_type',
                'jed_internal_note', 'a.jed_internal_note',
                'can_update', 'a.can_update',
                'video', 'a.video',
                'version', 'a.version',
                'uses_updater', 'a.uses_updater',
                'includes', 'a.includes',
                'approved', 'a.approved',
                'approved_time', 'a.approved_time',
                'second_contact_email', 'a.second_contact_email',
                'jed_checked', 'a.jed_checked',
                'uses_third_party', 'a.uses_third_party',
                'primary_category_id', 'a.primary_category_id',
                'logo', 'a.logo',
                'approved_notes', 'a.approved_notes',
                'approved_reason', 'a.approved_reason',
                'published_notes', 'a.published_notes',
                'published_reason', 'a.published_reason',
                'state', 'a.state',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Get an array of data items
     *
     * @return mixed Array of data items on success, false on failure.
     *
     * @since 4.0.0
     */
    public function getItems(): mixed
    {
        $items = parent::getItems();
        $db    = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select('COUNT(' . $db->quoteName('id') . ')')
            ->from($db->quoteName('#__jed_reviews'));

        array_walk(
            $items,
            static function ($item) use ($db, $query) {
                $query->clear('where')
                    ->where($db->quoteName('extension_id') . ' = ' . (int) $item->id)
                    ->where($db->quoteName('published') . ' = 1');
                $db->setQuery($query);
                $item->reviewCount = $db->loadResult();
            }
        );

        return $items;
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return   QueryInterface
     *
     * @since   4.0.0
     */
    protected function getListQuery(): QueryInterface
    {
        // Create a new query object.
        $db = $this->getDatabase();

        $query = $db->getQuery(true)

        // Select the required fields from the table.
            // $query->select('varied.description as description, varied.title as title, varied.alias as alias');
        //        $query->join('INNER', '#__jed_extension_varied_data AS varied ON varied.extension_id = a.id and varied.is_default_data=1');
        ->select(
            $db->quoteName(
                [
                    'a.id',
                    'varied.title',
                    'varied.alias',
                    'a.created_by',
                    'a.modified_on',
                    'a.created_on',
                    'a.checked_out',
                    'a.checked_out_time',
                    'a.approved',
                    'a.published',
                    'categories.title',
                    'users.name',
                    'staff.name',
                ],
                [
                    'id',
                    'title',
                    'alias',
                    'created_by',
                    'modified_on',
                    'created_on',
                    'checked_out',
                    'checked_out_time',
                    'approved',
                    'published',
                    'category',
                    'developer',
                    'editor',
                ]
            )
        )
          ->from($db->quoteName('#__jed_extensions', 'a'))
        ->leftJoin(
            $db->quoteName('#__categories', 'categories')
                . ' ON ' . $db->quoteName('categories.id') . ' = ' . $db->quoteName('a.primary_category_id')
        )
        ->leftJoin(
            $db->quoteName('#__users', 'users')
                . ' ON ' . $db->quoteName('users.id') . ' = ' . $db->quoteName('a.created_by')
        )
            ->leftJoin(
                $db->quoteName('#__users', 'staff')
                . ' ON ' . $db->quoteName('staff.id') . ' = ' . $db->quoteName('a.checked_out')
            )
            ->leftJoin(
                $db->quoteName('#__jed_extension_varied_data', 'varied')
                . ' ON ' . $db->quoteName('varied.extension_id') . ' = ' . $db->quoteName('a.id')
            )
            ->leftJoin(
                $db->quoteName('#__jed_extension_supply_options', 'supply_type')
                . ' ON ' . $db->quoteName('supply_type.id') . ' = ' . $db->quoteName('varied.supply_option_id')
            )
            ->select('GROUP_CONCAT(`supply_type`.`title`) as type');

        $query->where('varied.is_default_data=1');

        // Filter by published state
        $published = $this->getState('filter.state');

        if (is_numeric($published)) {
            $query->where('a.state = ' . (int) $published);
        } elseif (empty($published)) {
            $query->where('(a.state IN (0, 1))');
        }

        // Filter by search in title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->quote('%' . $db->escape($search, true) . '%');
                $query->where($db->quoteName('a.title') . ' LIKE ' . $search);
            }
        }

        $categoryIds = $this->getState('filter.category_id');

        if ($categoryIds) {
            $query->where($db->quoteName('a.primary_category_id') . ' IN (' . implode(',', $categoryIds) . ')');
        }

        $published = $this->getState('filter.published');

        if (is_numeric($published)) {
            $query->where($db->quoteName('a.state') . ' = ' . (int) $published);
        }

        $approved = $this->getState('filter.approved', '');

        if ($approved !== '') {
            $query->where($db->quoteName('a.approved') . ' = ' . $db->quote($approved));
        }

        $developerId = $this->getState('filter.developer_id');

        if (is_numeric($developerId)) {
            $query->where($db->quoteName('a.created_by') . ' = ' . (int) $developerId);
        }

        $includes = $this->getState('filter.includes');

        if ($includes && $includes[0] !== '') {
            $query->where($db->quoteName('types.type') . ' IN (' . implode(',', $db->quote($includes, false)) . ')');
        }

        $query->group($db->quoteName('a.id'));

        $query->order(
            $db->quoteName(
                $db->escape(
                    $this->getState('list.ordering', 'a.id')
                )
            ) . ' ' . $db->escape($this->getState('list.direction', 'DESC'))
        );
        //      echo($query->__toString());
        //      exit();
        return $query;
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
     * @return  string A store id.
     *
     * @since   4.0.0
     */
    protected function getStoreId($id = ''): string
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.state');


        return parent::getStoreId($id);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   Elements order
     * @param   string  $direction  Order direction
     *
     * @return void
     *
     * @since 4.0.0
     * @throws Exception
     *
     */
    protected function populateState($ordering = null, $direction = null)
    {
        // List state information.
        parent::populateState('id', 'ASC');

        $context = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $context);

        // Split context into component and optional section
        $parts = FieldsHelper::extract($context);

        if ($parts) {
            $this->setState('filter.component', $parts[0]);
            $this->setState('filter.section', $parts[1]);
        }
    }
}
