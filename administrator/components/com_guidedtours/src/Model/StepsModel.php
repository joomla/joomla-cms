<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\Guidedtours\Administrator\Helper\GuidedtoursHelper;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Methods supporting a list of steps records.
 *
 * @since 4.3.0
 */
class StepsModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array $config An optional associative array of configuration settings.
     *
     * @since 4.3.0
     * @see   \Joomla\CMS\MVC\Controller\BaseController
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'tour_id', 'a.tour_id',
                'title', 'a.title',
                'type', 'a.type',
                'description', 'a.description',
                'published', 'a.published',
                'ordering', 'a.ordering',
                'created_by', 'a.created_by',
                'modified', 'a.modified',
                'modified_by', 'a.modified_by',
                'note', 'a.note',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Provide a query to be used to evaluate if this is an Empty State, can be overridden in the model to provide granular control.
     *
     * @return QueryInterface
     *
     * @since 4.3.0
     */
    protected function getEmptyStateQuery()
    {
        $query = parent::getEmptyStateQuery();

        $tourId = $this->getState('filter.tour_id');

        if ($tourId) {
            $tourId = (int) $tourId;
            $query->where($this->getDatabase()->quoteName('a.tour_id') . ' = :tour_id')
                ->bind(':tour_id', $tourId, ParameterType::INTEGER);
        }

        return $query;
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string $ordering  An optional ordering field.
     * @param   string $direction An optional direction (asc|desc).
     *
     * @return void
     *
     * @since 4.3.0
     */
    protected function populateState($ordering = 'a.ordering', $direction = 'ASC')
    {
        $app = Factory::getApplication();

        $tourId = $app->getUserStateFromRequest($this->context . '.filter.tour_id', 'tour_id', 0, 'int');

        if (empty($tourId)) {
            $tourId = $app->getUserState('com_guidedtours.tour_id');
        }

        $this->setState('filter.tour_id', $tourId);

        // Keep the tour_id for adding new visits
        $app->setUserState('com_guidedtours.tour_id', $tourId);

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
     * @param   string $id A prefix for the store id.
     *
     * @return string  A store id.
     *
     * @since 4.3.0
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.tour_id');
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.published');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return QueryInterface
     *
     * @since 4.3.0
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.*'
            )
        );

        $query->from($db->quoteName('#__guidedtour_steps', 'a'));

        // Join with user table
        $query->select(
            [
                $db->quoteName('uc.name', 'editor'),
            ]
        )
            ->join('LEFT', $db->quoteName('#__users', 'uc'), $db->quoteName('uc.id') . ' = ' . $db->quoteName('a.checked_out'));

        $tourId = $this->getState('filter.tour_id');

        if (is_numeric($tourId)) {
            $tourId = (int) $tourId;
            $query->where($db->quoteName('a.tour_id') . ' = :tour_id')
                ->bind(':tour_id', $tourId, ParameterType::INTEGER);
        } elseif (\is_array($tourId)) {
            $tourId = ArrayHelper::toInteger($tourId);
            $query->whereIn($db->quoteName('a.tour_id'), $tourId);
        }

        // Published state
        $published = (string) $this->getState('filter.published');

        if (is_numeric($published)) {
            $query->where($db->quoteName('a.published') . ' = :published');
            $query->bind(':published', $published, ParameterType::INTEGER);
        } elseif ($published === '') {
            $query->where('(' . $db->quoteName('a.published') . ' = 0 OR ' . $db->quoteName('a.published') . ' = 1)');
        }

        // Filter by search in title.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $search = (int) substr($search, 3);
                $query->where($db->quoteName('a.id') . ' = :search')
                    ->bind(':search', $search, ParameterType::INTEGER);
            } elseif (stripos($search, 'description:') === 0) {
                $search = '%' . substr($search, 12) . '%';
                $query->where('(' . $db->quoteName('a.description') . ' LIKE :search1)')
                    ->bind([':search1'], $search);
            } else {
                $search = '%' . str_replace(' ', '%', trim($search)) . '%';
                $query->where(
                    '(' . $db->quoteName('a.title') . ' LIKE :search1'
                    . ' OR ' . $db->quoteName('a.description') . ' LIKE :search2'
                    . ' OR ' . $db->quoteName('a.note') . ' LIKE :search3)'
                )
                    ->bind([':search1', ':search2', ':search3'], $search);
            }
        }

        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', 'a.ordering');
        $orderDirn = $this->state->get('list.direction', 'ASC');

        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }

    /**
     * Method to get an array of data items.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   4.3.0
     */
    public function getItems()
    {
        $items = parent::getItems();

        $tourLanguageLoaded = false;
        foreach ($items as $item) {
            if (!$tourLanguageLoaded) {
                $app    = Factory::getApplication();
                $tourId = $item->tour_id;

                /** @var \Joomla\Component\Guidedtours\Administrator\Model\TourModel $tourModel */
                $tourModel = $app->bootComponent('com_guidedtours')
                                 ->getMVCFactory()->createModel('Tour', 'Administrator', [ 'ignore_request' => true ]);

                $tour = $tourModel->getItem($tourId);

                GuidedtoursHelper::loadTranslationFiles($tour->uid, true);

                $tourLanguageLoaded = true;
            }

            $item->title       = Text::_($item->title);
            $item->description = Text::_($item->description);

            if (isset($item->params)) {
                $params = new Registry($item->params);
                if (!empty($item->params->requiredvalue)) {
                    $item->params->requiredvalue = Text::_($item->params->requiredvalue);
                }
            }
        }

        return $items;
    }
}
