<?php

/**
 * @package    JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Site\Model;

// No direct access.
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Jed\Component\Jed\Administrator\MediaHandling\ImageSize;
use Jed\Component\Jed\Administrator\Traits\ExtensionUtilities;
use Jed\Component\Jed\Site\Helper\JedHelper;
use Jed\Component\Jed\Site\Helper\JedscoreHelper;
use Jed\Component\Jed\Site\Helper\JedtrophyHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Database\QueryInterface;

/**
 * Methods supporting a list of Jed records.
 *
 * @since  4.0.0
 */
class ExtensionsModel extends ListModel
{
    use ExtensionUtilities;

    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see    JController
     * @since  4.0.0
     * @throws Exception
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
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   Elements order
     * @param   string  $direction  Order direction
     *
     * @return  void
     *
     * @throws  Exception
     *
     * @since   4.0.0
     */
    protected function populateState($ordering = null, $direction = null)
    {
        // List state information.
        parent::populateState('a.id', 'ASC');

        $app  = Factory::getApplication();
        $list = $app->getUserState($this->context . '.list');

        $value         = $app->getUserState($this->context . '.list.limit', $app->get('list_limit', 25));
        $list['limit'] = $value;

        $this->setState('list.limit', $value);

        $value = $app->input->get('limitstart', 0, 'uint');
        $this->setState('list.start', $value);

        $ordering  = $this->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', 'a.id');
        $direction = strtoupper($this->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', 'ASC'));

        if (!empty($ordering) || !empty($direction)) {
            $list['fullordering'] = $ordering . ' ' . $direction;
        }

        $app->setUserState($this->context . '.list', $list);

        $this->setState($this->context . 'catid', $app->input->getInt('id', 0));

        $context = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $context);

        // Split context into component and optional section
        $parts = FieldsHelper::extract($context);

        if ($parts) {
            $this->setState('filter.component', $parts[0]);
            $this->setState('filter.section', $parts[1]);
        }
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  QueryInterface
     *
     * @since   4.0.0
     */
    protected function getListQuery(): QueryInterface
    {
        // Create a new query object.
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'DISTINCT a.*'
            )
        );

        $query->from('#__jed_extensions AS a');

        $query->select('cat.title AS category_title');
        $query->join('INNER', '#__categories AS cat ON cat.id=a.primary_category_id');
        // Join over the users for the checked out user.
        $query->select('uc.name AS uEditor');
        $query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

        // Join over the created by field 'created_by'
        $query->select('created_by.name AS developer');
        $query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

        // Join over the created by field 'modified_by'
        $query->join('LEFT', '#__users AS modified_by ON modified_by.id = a.modified_by');

        //Join to Varied Data to get Default descriptive text
        $query->select('varied.description as description, varied.title as title, varied.alias as alias');
        $query->join('INNER', '#__jed_extension_varied_data AS varied ON varied.extension_id = a.id and varied.is_default_data=1');


        if (!JedHelper::getUser()->authorise('core.edit', 'com_jed')) {
            $query->where('a.state = 1');
        } else {
            $query->where('(a.state IN (0, 1))'); //Published 0=unpublished, 1=published, 2=unpublished by author
        }

        // Filter by search in title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                $query->where('(title LIKE ' . $search);
            }
        }


        $category = $this->state->get($this->context . 'catid');
        if (!empty($category)) {
            $query->where('a.primary_category_id =' . $category);
        }

        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', 'a.id');
        $orderDirn = $this->state->get('list.direction', 'ASC');

        if ($orderCol && $orderDirn) {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        return $query;
    }

    /**
     * Get array of review scores for extension
     *
     * @param   int  $extension_id
     *
     * @return array
     *
     * @since 4.0.0
     */
    public function getScores(int $extension_id): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from($db->quoteName('#__jed_extension_scores'))
            ->where($db->quoteName('extension_id') . ' = ' . $db->quote($extension_id));

        $db->setQuery($query);
        return $db->loadObjectList();
    }

    /**
     * Method to get an array of data items
     *
     * @return  mixed An array of data on success, false on failure.
     *
     * @since 4.0.0
     */
    public function getItems()
    {
        $items = parent::getItems();
        foreach ($items as $item) {
            //echo "<pre>";print_r($item);echo "</pre>";exit();

            $item->category_hierarchy = $this->getCategoryHierarchy($item->primary_category_id);

            if (!empty($item->logo)) {
                $item->logo = JedHelper::formatImage($item->logo, ImageSize::SMALL);
            }

            $item->scores            = $this->getScores($item->id);
            $item->number_of_reviews = 0;
            $score                   = 0;
            $supplycounter           = 0;
            $supplytype              = '';
            foreach ($item->scores as $s) {
                $supplycounter = $supplycounter + 1;
                if ($s->supply_option_id == 1) {
                    $supplytype .= 'Free';
                }
                if ($s->supply_option_id == 2) {
                    $comma = '';
                    if ($supplytype <> '') {
                        $comma = ', ';
                    }

                    $supplytype .= $comma . 'Paid';
                }
                $score                   = $score + $s->functionality_score;
                $score                   = $score + $s->ease_of_use_score;
                $score                   = $score + $s->support_score;
                $score                   = $score + $s->value_for_money_score;
                $score                   = $score + $s->documentation_score;
                $item->number_of_reviews = $item->number_of_reviews + $s->number_of_reviews;
            }
            $item->type  = $supplytype;
            $score       = $score / $supplycounter;
            $item->score = floor($score / 5);
            //echo "<pre>";print_r($item);echo "</pre>";exit();
            $item->score_string = JedscoreHelper::getStars($item->score);
            if ($item->number_of_reviews == 0) {
                $item->review_string = '';
            } elseif ($item->number_of_reviews == 1) {
                $item->review_string = '<span>' . $item->number_of_reviews . ' review</span>';
            } elseif ($item->number_of_reviews > 1) {
                $item->review_string = '<span>' . $item->number_of_reviews . ' reviews</span>';
            }
            //echo "<pre>";print_r($item);echo "</pre>";exit();

            // https://extensions.joomla.org/cache/fab_image/27824_resizeDown400px175px16.png

            if (!empty($item->uses_updater)) {
                $item->uses_updater = Text::_('COM_JED_EXTENSIONS_USES_UPDATER_OPTION_' . strtoupper($item->uses_updater));
            }
            $item->version = JedtrophyHelper::getTrophyVersionsString($item->joomla_versions);
        }
        $items = array_values($items);
        array_multisort(array_column($items, "number_of_reviews"), SORT_DESC, $items);
        //echo "<pre>";print_r($items);echo "</pre>";exit();
        return $items;
    }

    /**
     * Overrides the default function to check Date fields format, identified by
     * "_dateformat" suffix, and erases the field if it's not correct.
     *
     * @return void
     *
     * @since 4.0.0
     * @throws Exception
     */
    protected function loadFormData()
    {
        $app              = Factory::getApplication();
        $filters          = $app->getUserState($this->context . '.filter', []);
        $error_dateformat = false;

        foreach ($filters as $key => $value) {
            if (strpos($key, '_dateformat') && !empty($value) && JedHelper::isValidDate($value) == null) {
                $filters[$key]    = '';
                $error_dateformat = true;
            }
        }

        if ($error_dateformat) {
            $app->enqueueMessage(Text::_("COM_JED_SEARCH_FILTER_DATE_FORMAT"), "warning");
            $app->setUserState($this->context . '.filter', $filters);
        }

        return parent::loadFormData();
    }


    /**
     * Retrieve a list of developers matching a search query.
     *
     * @param   string  $search  The string to filter on
     *
     * @return  array List of developers.
     *
     * @since   4.0.0
     */
    public function getDevelopers(string $search): array
    {
        $db    =  Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select(
                $db->quoteName(
                    [
                        'users.id',
                        'users.name',
                    ]
                )
            )
            ->from($db->quoteName('#__users', 'users'))
            ->leftJoin(
                $db->quoteName('#__jed_extensions', 'extensions')
                . ' ON ' . $db->quoteName('extensions.created_by') . ' = ' . $db->quoteName('users.id')
            )
            ->where($db->quoteName('users.name') . ' LIKE ' . $db->quote('%' . $search . '%'))
            ->group($db->quoteName('users.id'))
            ->order($db->quoteName('users.name'));
        $db->setQuery($query);

        return $db->loadObjectList();
    }

    /**
     * Get the used extension types.
     *
     * @param   int  $extensionId  The extension ID to get the types for
     *
     * @return  array  List of used extension types.
     *
     * @since   4.0.0
     */
    public function getExtensionTypes(int $extensionId): array
    {
        $db    =  Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select($db->quoteName('type'))
            ->from($db->quoteName('#__jed_extensions_types'))
            ->where($db->quoteName('extension_id') . ' = ' . $extensionId);
        $db->setQuery($query);

        return $db->loadColumn();
    }

    /**
     * Get the images.
     *
     * @param   int  $extensionId  The extension ID to get the images for
     *
     * @return  array  List of used images.
     *
     * @since   4.0.0
     */
    public function getImages(int $extensionId): array
    {
        $db    =  Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select($db->quoteName('filename'))
            ->from($db->quoteName('#__jed_extensions_images'))
            ->where($db->quoteName('extension_id') . ' = ' . $extensionId)
            ->order($db->quoteName('order'));
        $db->setQuery($query);

        $items  = $db->loadObjectList();
        $images = [];

        array_walk(
            $items,
            static function ($item, $key) use (&$images) {
                $images['images' . $key]['image'] = $item->filename;
            }
        );

        return $images;
    }


    /**
     * Get the related categories.
     *
     * @param   int  $extensionId  The extension ID to get the categories for
     *
     * @return  array  List of related categories.
     *
     * @since   4.0.0
     */
    public function getRelatedCategories(int $extensionId): array
    {
        $db    =  Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select($db->quoteName('category_id'))
            ->from($db->quoteName('#__jed_extensions_categories'))
            ->where($db->quoteName('extension_id') . ' = ' . $extensionId);
        $db->setQuery($query);

        return $db->loadColumn();
    }

    /**
     * Get the supported PHP versions.
     *
     * @param   int     $extensionId  The extension ID to get the PHP versions for
     * @param   string  $type         The type of version to get
     *
     * @return  array  List of supported PHP versions.
     *
     * @since   4.0.0
     */
    public function getVersions(int $extensionId, string $type): array
    {
        $db    =  Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select($db->quoteName('version'))
            ->from($db->quoteName('#__jed_extensions_' . $type . '_versions'))
            ->where($db->quoteName('extension_id') . ' = ' . $extensionId);

        $db->setQuery($query);

        return $db->loadColumn();
    }
}
