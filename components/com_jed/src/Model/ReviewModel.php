<?php

/**
 * @package        JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Site\Model;

// No direct access.
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Jed\Component\Jed\Site\Helper\JedHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;

/**
 * Jed model.
 *
 * @since  4.0.0
 */
class ReviewModel extends ItemModel
{
    public const PROCESSING_WINDOW = 30;
    /**
     * Log how the score is generated.
     *
     * @var array
     *
     * @since 4.0.0
     */
    public array $log = [];
    /**
     * Admin test mode - outputs the log
     * @var bool
     *
     * @since 4.0.0
     */
    public bool $testMode = false;
    /**
     * Set to false in review import from jed_migrate.
     * Determines if the extension review score should be calculated
     * when the model is stored
     *
     * @var bool
     *
     * @since 4.0.0
     */
    public bool $doScore = true;
    /**
     * Message to show if user can not add a review
     *
     * @var string
     *
     * @since 4.0.0
     */
    public string $accessMsg = '';
    /**
     * Owner id - used in review query
     *
     * @var int|null
     *
     * @since 4.0.0
     */
    protected ?int $owner_id = null;
    /**
     * Fields to score on
     *
     * @var array
     *
     * @since 4.0.0
     */
    protected ?array $score_fields = null;
    /**
     * Fields to score on
     *
     * @var array
     *
     * @since 4.0.0
     */
    protected array $ratings = ['functionality', 'ease_of_use', 'support', 'documentation', 'value_for_money'];
    /**
     * Count all reviews regardless of language filter
     *
     * @var int
     *
     * @since 4.0.0
     */
    protected $totalAll = null;

    protected int $defaultLimit = 10;

    // 30 Minutes of Processing Window
    /** Data Table
     * @var string
     * @since 4.0.0
     **/
    private string $dbtable = "#__jed_reviews";

    /**
     * Method to check in an item.
     *
     * @param   integer  $id  The id of the row to check out.
     *
     * @return  boolean True on success, false on failure.
     *
     * @since   4.0.0
     * @throws Exception
     */
    public function checkin($id = null): bool
    {
        // Get the id.
        $id = (!empty($id)) ? $id : (int)$this->getState('review.id');
        if ($id || JedHelper::userIDItem($id, $this->dbtable) || JedHelper::isAdminOrSuperUser()) {
            if ($id) {
                // Initialise the table
                $table = $this->getTable();

                // Attempt to check the row in.
                if (method_exists($table, 'checkin')) {
                    if (!$table->checkin($id)) {
                        return false;
                    }
                }
            }

            return true;
        } else {
            throw new Exception(Text::_("JERROR_ALERTNOAUTHOR"), 401);
        }
    }

    /**
     * Method to check out an item for editing.
     *
     * @param   integer  $id  The id of the row to check out.
     *
     * @return  boolean True on success, false on failure.
     *
     * @since   4.0.0
     * @throws Exception
     */
    public function checkout($id = null): bool
    {
        // Get the user id.
        $id = (!empty($id)) ? $id : (int)$this->getState('review.id');

        if ($id || JedHelper::userIDItem($id, $this->dbtable) || JedHelper::isAdminOrSuperUser()) {
            if ($id) {
                // Initialise the table
                $table = $this->getTable();

                // Get the current user object.
                $user = JedHelper::getUser();

                // Attempt to check the row out.
                if (method_exists($table, 'checkout')) {
                    if (!$table->checkout($user->get('id'), $id)) {
                        return false;
                    }
                }
            }

            return true;
        } else {
            throw new Exception(Text::_("JERROR_ALERTNOAUTHOR"), 401);
        }
    }

    /**
     * Method to delete an item
     *
     * @param   int  $id  Element id
     *
     * @return  bool
     * @since 4.0.0
     * @throws Exception
     *
     */
    public function delete(int $id): bool
    {
        $table = $this->getTable();

        if (empty($result) || JedHelper::isAdminOrSuperUser() || $table->created_by == JedHelper::getUser()->id) {
            return $table->delete($id);
        } else {
            throw new Exception(Text::_("JERROR_ALERTNOAUTHOR"), 401);
        }
    }

    /**
     * Method to get an object.
     *
     * @param   integer  $pk  The id of the object to get.
     *
     * @return  mixed    Object on success, false on failure.
     *
     * @since 4.0.0
     * @throws Exception
     *
     */
    public function getItem($pk = null)
    {
        if ($this->item === null) {
            $this->item = false;

            if (empty($pk)) {
                $pk = $this->getState('review.id');
            }

            // Get a level row instance.
            $table = $this->getTable();

            // Attempt to load the row.
            if ($table && $table->load($pk)) {
                if (empty($result) || JedHelper::isAdminOrSuperUser()) {
                    // Check published state.
                    if ($published = $this->getState('filter.published')) {
                        if (isset($table->state) && $table->state != $published) {
                            throw new Exception(Text::_('COM_JED_ITEM_NOT_LOADED'), 403);
                        }
                    }

                    // Convert the Table to a clean CMSObject.
                    $properties = $table->getProperties(1);
                    $this->item = ArrayHelper::toObject($properties, CMSObject::class);
                } else {
                    throw new Exception(Text::_("JERROR_ALERTNOAUTHOR"), 401);
                }
            }

            if (empty($this->item)) {
                throw new Exception(Text::_('COM_JED_ITEM_NOT_LOADED'), 404);
            }
        }


        if (isset($this->item->extension_id) && $this->item->extension_id != '') {
            if (is_object($this->item->extension_id)) {
                $this->item->extension_id = ArrayHelper::fromObject($this->item->extension_id);
            }

            $values = (is_array($this->item->extension_id)) ? $this->item->extension_id : explode(
                ',',
                $this->item->extension_id
            );

            $textValue = [];

            foreach ($values as $value) {
                $db    = Factory::getDbo();
                $query = $db->getQuery(true);

                $query
                    ->select('`je`.`title`')
                    ->from($db->quoteName('#__jed_extensions', 'je'))
                    ->where($db->quoteName('id') . ' = ' . $db->quote($value));

                $db->setQuery($query);
                $results = $db->loadObject();

                if ($results) {
                    $textValue[] = $results->title;
                }
            }

            $this->item->extension_id = !empty($textValue) ? implode(', ', $textValue) : $this->item->extension_id;
        }

        if (isset($this->item->supply_option_id) && $this->item->supply_option_id != '') {
            if (is_object($this->item->supply_option_id)) {
                $this->item->supply_option_id = ArrayHelper::fromObject($this->item->supply_option_id);
            }

            $values = (is_array($this->item->supply_option_id)) ? $this->item->supply_option_id : explode(
                ',',
                $this->item->supply_option_id
            );

            $textValue = [];

            foreach ($values as $value) {
                $db    = Factory::getDbo();
                $query = $db->getQuery(true);

                $query
                    ->select('`jso`.`title`')
                    ->from($db->quoteName('#__jed_extension_supply_options', 'jso'))
                    ->where($db->quoteName('id') . ' = ' . $db->quote($value));

                $db->setQuery($query);
                $results = $db->loadObject();

                if ($results) {
                    $textValue[] = $results->title;
                }
            }

            $this->item->supply_option_id = !empty($textValue) ? implode(
                ', ',
                $textValue
            ) : $this->item->supply_option_id;
        }

        if (isset($this->item->created_by)) {
            $this->item->created_by_name = JedHelper::getUser($this->item->created_by)->name;
        }

        return $this->item;
    }

    /**
     * Get the id of an item by alias
     *
     * @param   string  $alias  Item alias
     *
     * @return  mixed
     *
     * @since 4.0.0
     * @throws Exception
     */
    public function getItemIdByAlias(string $alias)
    {
        $table      = $this->getTable();
        $properties = $table->getProperties();
        $result     = null;
        $aliasKey   = null;

        $aliasKey = JedHelper::getAliasFieldNameByView('review');


        if (key_exists('alias', $properties)) {
            $table->load(['alias' => $alias]);
            $result = $table->id;
        } elseif (isset($aliasKey) && key_exists($aliasKey, $properties)) {
            $table->load([$aliasKey => $alias]);
            $result = $table->id;
        }
        if (empty($result) || JedHelper::isAdminOrSuperUser() || $table->created_by == JedHelper::getUser()->id) {
            return $result;
        } else {
            throw new Exception(Text::_("JERROR_ALERTNOAUTHOR"), 401);
        }
    }

    /**
     * Get an instance of Table class
     *
     * @param   string  $name     Name of the Table class to get an instance of.
     * @param   string  $prefix   Prefix for the table class name. Optional.
     * @param   array   $options  Array of configuration values for the Table object. Optional.
     *
     * @return  Table|bool Table if success, false on failure.
     *
     * @since 4.0.0
     * @throws Exception
     */
    public function getTable($name = 'Review', $prefix = 'Administrator', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since   4.0.0
     *
     * @throws Exception
     */
    protected function populateState()
    {
        $app  = Factory::getApplication('com_jed');
        $user = JedHelper::getUser();

        // Check published state
        if ((!$user->authorise('core.edit.state', 'com_jed')) && (!$user->authorise('core.edit', 'com_jed'))) {
            $this->setState('filter.published', 1);
            $this->setState('filter.archived', 2);
        }

        // Load state from the request userState on edit or from the passed variable on default
        if (Factory::getApplication()->input->get('layout') == 'edit') {
            $id = Factory::getApplication()->getUserState('com_jed.edit.review.id');
        } else {
            $id = Factory::getApplication()->input->get('id');
            Factory::getApplication()->setUserState('com_jed.edit.review.id', $id);
        }

        $this->setState('review.id', $id);

        // Load the parameters.
        $params       = $app->getParams();
        $params_array = $params->toArray();

        if (isset($params_array['item_id'])) {
            $this->setState('review.id', $params_array['item_id']);
        }

        $this->setState('params', $params);
    }

    /**
     * Publish the element
     *
     * @param   int  $id     Item id
     * @param   int  $state  Publish state
     *
     * @return  boolean
     *
     * @since 4.0.0
     * @throws Exception
     */
    public function publish($id, $state)
    {
        $table = $this->getTable();
        if ($id || JedHelper::userIDItem($id, $this->dbtable) || JedHelper::isAdminOrSuperUser()) {
            $table->load($id);
            $table->state = $state;

            return $table->store();
        } else {
            throw new Exception(Text::_("JERROR_ALERTNOAUTHOR"), 401);
        }
    }

    /**
     * Constructor
     *
     * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
     *
     * @since   3.0
     * @throws  Exception
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->score_fields = [
            'functionality'   => Text::_('COM_JED_REVIEWS_FIELD_FUNCTIONALITY_LABEL'),
            'ease_of_use'     => Text::_('COM_JED_REVIEWS_FIELD_EASE_OF_USE_LABEL'),
            'support'         => Text::_('COM_JED_REVIEWS_FIELD_SUPPORT_LABEL'),
            'documentation'   => Text::_('COM_JED_REVIEWS_FIELD_DOCUMENTATION_LABEL'),
            'value_for_money' => Text::_('COM_JED_REVIEWS_FIELD_VALUE_FOR_MONEY_LABEL'),
        ];
    }
}
