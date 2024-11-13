<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\Model;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
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
 * Model class for Tours
 *
 * @since  4.3.0
 */
class ToursModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array                 $config   An optional associative array of configuration settings.
     * @param   ?MVCFactoryInterface  $factory  The factory.
     *
     * @see     JController
     * @since   4.3.0
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'title', 'a.title',
                'access', 'a.access', 'access_level',
                'description', 'a.description',
                'published', 'a.published',
                'language', 'a.language',
                'ordering', 'a.ordering',
                'extensions', 'a.extensions',
                'created_by', 'a.created_by',
                'modified', 'a.modified',
                'modified_by', 'a.modified_by',
                'note', 'a.note',
            ];
        }

        parent::__construct($config, $factory);
    }

    /**
     * Method to auto-populate the model state.
     *
     * This method should only be called once per instantiation and is designed
     * to be called on the first call to the getState() method unless the model
     * configuration flag to ignore the request is set.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since  4.3.0
     */
    protected function populateState($ordering = 'a.ordering', $direction = 'ASC')
    {
        parent::populateState($ordering, $direction);
    }

    /**
     * Get the filter form
     *
     * @param   array    $data      data
     * @param   boolean  $loadData  load current data
     *
     * @return  \JForm|false  the JForm object or false
     *
     * @since  4.3.0
     */
    public function getFilterForm($data = [], $loadData = true)
    {
        $form = parent::getFilterForm($data, $loadData);

        if ($form) {
            $form->setValue('extension', null, $this->getState('filter.extension'));
        }

        return $form;
    }
    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  QueryInterface  The query to database.
     *
     * @since  4.3.0
     */
    public function getListQuery()
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

        $subQuery = $db->getQuery(true)
            ->select('COUNT(' . $db->quoteName('s.id') . ')')
            ->from($db->quoteName('#__guidedtour_steps', 's'))
            ->where(
                [
                    $db->quoteName('s.tour_id') . ' = ' . $db->quoteName('a.id'),
                    $db->quoteName('s.published') . ' = 1',
                ]
            );

        $query->select('(' . $subQuery . ') AS ' . $db->quoteName('steps_count'))
            ->from($db->quoteName('#__guidedtours', 'a'));

        // Join with user table
        $query->select(
            [
                $db->quoteName('uc.name', 'editor'),
            ]
        )
            ->join('LEFT', $db->quoteName('#__users', 'uc'), $db->quoteName('uc.id') . ' = ' . $db->quoteName('a.checked_out'));

        // Join with language table
        $query->select(
            [
                $db->quoteName('l.title', 'language_title'),
                $db->quoteName('l.image', 'language_image'),
            ]
        )
            ->join('LEFT', $db->quoteName('#__languages', 'l'), $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('a.language'));

        // Join access table
        $query->select(
            [
                $db->quoteName('ag.title', 'access_level'),
            ]
        )
            ->join('LEFT', $db->quoteName('#__viewlevels', 'ag'), $db->quoteName('ag.id') . ' = ' . $db->quoteName('a.access'));

        // Filter by extension
        if ($extension = $this->getState('filter.extension')) {
            $extension = '%' . $extension . '%';
            $all       = '%*%';
            $query->where(
                '(' . $db->quoteName('a.extensions') . ' LIKE :all  OR ' .
                $db->quoteName('a.extensions') . ' LIKE :extensions)'
            )
                ->bind([':all'], $all)
                ->bind([':extensions'], $extension);
        }

        $status = (string) $this->getState('filter.published');

        // Filter by status
        if (is_numeric($status)) {
            $status = (int) $status;
            $query->where($db->quoteName('a.published') . ' = :published')
                ->bind(':published', $status, ParameterType::INTEGER);
        } elseif ($status === '') {
            $query->where($db->quoteName('a.published') . ' IN (0, 1)');
        }

        // Filter by access level.
        $access = $this->getState('filter.access');

        if (is_numeric($access)) {
            $access = (int) $access;
            $query->where($db->quoteName('a.access') . ' = :access')
                ->bind(':access', $access, ParameterType::INTEGER);
        } elseif (\is_array($access)) {
            $access = ArrayHelper::toInteger($access);
            $query->whereIn($db->quoteName('a.access'), $access);
        }

        // Filter on the language, or all.
        if ($language = $this->getState('filter.language')) {
            $language = (array) $language;

            $query->whereIn($db->quoteName('a.language'), $language, ParameterType::STRING);
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
        $orderDirn = strtoupper($this->state->get('list.direction', 'ASC'));

        $query->order($db->escape($orderCol) . ' ' . ($orderDirn === 'DESC' ? 'DESC' : 'ASC'));

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

        foreach ($items as & $item) {
            if (!empty($item->uid)) {
                GuidedtoursHelper::loadTranslationFiles($item->uid, false);
            }
            $item->title       = Text::_($item->title);
            $item->description = Text::_($item->description);
            $item->extensions  = (new Registry($item->extensions))->toArray();
        }
        unset($item);

        return $items;
    }
}
