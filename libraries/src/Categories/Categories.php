<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Categories;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\Exception\DatabaseNotFoundException;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Categories Class.
 *
 * @since  1.6
 */
class Categories implements CategoryInterface, DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * Array to hold the object instances
     *
     * @var    Categories[]
     * @since  1.6
     */
    public static $instances = [];

    /**
     * Array of category nodes
     *
     * @var    CategoryNode[]
     * @since  1.6
     */
    protected $_nodes;

    /**
     * Array of checked categories -- used to save values when _nodes are null
     *
     * @var    boolean[]
     * @since  1.6
     */
    protected $_checkedCategories;

    /**
     * Name of the extension the categories belong to
     *
     * @var    string
     * @since  1.6
     */
    protected $_extension;

    /**
     * Name of the linked content table to get category content count
     *
     * @var    string
     * @since  1.6
     */
    protected $_table;

    /**
     * Name of the category field
     *
     * @var    string
     * @since  1.6
     */
    protected $_field;

    /**
     * Name of the key field
     *
     * @var    string
     * @since  1.6
     */
    protected $_key;

    /**
     * Name of the items state field
     *
     * @var    string
     * @since  1.6
     */
    protected $_statefield;

    /**
     * Array of options
     *
     * @var    array
     * @since  1.6
     */
    protected $_options = [];

    /**
     * Class constructor
     *
     * @param   array  $options  Array of options
     *
     * @since   1.6
     */
    public function __construct($options)
    {
        $this->_extension  = $options['extension'];
        $this->_table      = $options['table'];
        $this->_field      = isset($options['field']) && $options['field'] ? $options['field'] : 'catid';
        $this->_key        = isset($options['key']) && $options['key'] ? $options['key'] : 'id';
        $this->_statefield = $options['statefield'] ?? 'state';

        $options['access']      ??= 'true';
        $options['published']   ??= 1;
        $options['countItems']  ??= 0;
        $options['currentlang'] = Multilanguage::isEnabled() ? Factory::getLanguage()->getTag() : 0;

        $this->_options = $options;
    }

    /**
     * Returns a reference to a Categories object
     *
     * @param   string  $extension  Name of the categories extension
     * @param   array   $options    An array of options
     *
     * @return  Categories|boolean  Categories object on success, boolean false if an object does not exist
     *
     * @since       1.6
     *
     * @deprecated  4.0 will be removed in 6.0
     *              Use the ComponentInterface to get the categories
     *              Example: Factory::getApplication()->bootComponent($component)->getCategory($options, $section);
     */
    public static function getInstance($extension, $options = [])
    {
        $hash = md5(strtolower($extension) . serialize($options));

        if (isset(self::$instances[$hash])) {
            return self::$instances[$hash];
        }

        $categories = null;

        try {
            $parts = explode('.', $extension, 2);

            $component = Factory::getApplication()->bootComponent($parts[0]);

            if ($component instanceof CategoryServiceInterface) {
                $categories = $component->getCategory($options, \count($parts) > 1 ? $parts[1] : '');
            }
        } catch (SectionNotFoundException) {
            $categories = null;
        }

        self::$instances[$hash] = $categories;

        return self::$instances[$hash];
    }

    /**
     * Loads a specific category and all its children in a CategoryNode object.
     *
     * @param   mixed    $id         an optional id integer or equal to 'root'
     * @param   boolean  $forceload  True to force  the _load method to execute
     *
     * @return  CategoryNode|null  CategoryNode object or null if $id is not valid
     *
     * @since   1.6
     */
    public function get($id = 'root', $forceload = false)
    {
        if ($id !== 'root') {
            $id = (int) $id;

            if ($id == 0) {
                $id = 'root';
            }
        }

        // If this $id has not been processed yet, execute the _load method
        if ((!isset($this->_nodes[$id]) && !isset($this->_checkedCategories[$id])) || $forceload) {
            $this->_load($id);
        }

        // If we already have a value in _nodes for this $id, then use it.
        if (isset($this->_nodes[$id])) {
            return $this->_nodes[$id];
        }

        return null;
    }

    /**
     * Returns the extension of the category.
     *
     * @return   string  The extension
     *
     * @since   3.9.0
     */
    public function getExtension()
    {
        return $this->_extension;
    }

    /**
     * Returns options.
     *
     * @return  array
     *
     * @since   4.3.2
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Load method
     *
     * @param   int|string  $id  Id of category to load
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function _load($id)
    {
        try {
            $db = $this->getDatabase();
        } catch (DatabaseNotFoundException) {
            @trigger_error('Database must be set, this will not be caught anymore in 5.0.', E_USER_DEPRECATED);
            $db = Factory::getContainer()->get(DatabaseInterface::class);
        }

        $app       = Factory::getApplication();
        $user      = Factory::getUser();
        $extension = $this->_extension;

        if ($id !== 'root') {
            $id = (int) $id;

            if ($id === 0) {
                $id = 'root';
            }
        }

        // Record that has this $id has been checked
        $this->_checkedCategories[$id] = true;

        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('c.id'),
                    $db->quoteName('c.asset_id'),
                    $db->quoteName('c.access'),
                    $db->quoteName('c.alias'),
                    $db->quoteName('c.checked_out'),
                    $db->quoteName('c.checked_out_time'),
                    $db->quoteName('c.created_time'),
                    $db->quoteName('c.created_user_id'),
                    $db->quoteName('c.description'),
                    $db->quoteName('c.extension'),
                    $db->quoteName('c.hits'),
                    $db->quoteName('c.language'),
                    $db->quoteName('c.level'),
                    $db->quoteName('c.lft'),
                    $db->quoteName('c.metadata'),
                    $db->quoteName('c.metadesc'),
                    $db->quoteName('c.metakey'),
                    $db->quoteName('c.modified_time'),
                    $db->quoteName('c.note'),
                    $db->quoteName('c.params'),
                    $db->quoteName('c.parent_id'),
                    $db->quoteName('c.path'),
                    $db->quoteName('c.published'),
                    $db->quoteName('c.rgt'),
                    $db->quoteName('c.title'),
                    $db->quoteName('c.modified_user_id'),
                    $db->quoteName('c.version'),
                ]
            );

        $case_when = ' CASE WHEN ';
        $case_when .= $query->charLength($db->quoteName('c.alias'), '!=', '0');
        $case_when .= ' THEN ';
        $c_id = $query->castAsChar($db->quoteName('c.id'));
        $case_when .= $query->concatenate([$c_id, $db->quoteName('c.alias')], ':');
        $case_when .= ' ELSE ';
        $case_when .= $c_id . ' END as ' . $db->quoteName('slug');

        $query->select($case_when)
            ->where('(' . $db->quoteName('c.extension') . ' = :extension OR ' . $db->quoteName('c.extension') . ' = ' . $db->quote('system') . ')')
            ->bind(':extension', $extension);

        if ($this->_options['access']) {
            $groups = $user->getAuthorisedViewLevels();
            $query->whereIn($db->quoteName('c.access'), $groups);
        }

        if ($this->_options['published'] == 1) {
            $query->where($db->quoteName('c.published') . ' = 1');
        }

        $query->order($db->quoteName('c.lft'));

        // Note: s for selected id
        if ($id !== 'root') {
            // Get the selected category
            $query->from($db->quoteName('#__categories', 's'))
                ->where($db->quoteName('s.id') . ' = :id')
                ->bind(':id', $id, ParameterType::INTEGER);

            if ($app->isClient('site') && Multilanguage::isEnabled()) {
                // For the most part, we use c.lft column, which index is properly used instead of c.rgt
                $query->join(
                    'INNER',
                    $db->quoteName('#__categories', 'c'),
                    '(' . $db->quoteName('s.lft') . ' < ' . $db->quoteName('c.lft')
                    . ' AND ' . $db->quoteName('c.lft') . ' < ' . $db->quoteName('s.rgt')
                    . ' AND ' . $db->quoteName('c.language')
                    . ' IN (' . implode(',', $query->bindArray([Factory::getLanguage()->getTag(), '*'], ParameterType::STRING)) . '))'
                    . ' OR (' . $db->quoteName('c.lft') . ' <= ' . $db->quoteName('s.lft')
                    . ' AND ' . $db->quoteName('s.rgt') . ' <= ' . $db->quoteName('c.rgt') . ')'
                );
            } else {
                $query->join(
                    'INNER',
                    $db->quoteName('#__categories', 'c'),
                    '(' . $db->quoteName('s.lft') . ' <= ' . $db->quoteName('c.lft')
                    . ' AND ' . $db->quoteName('c.lft') . ' < ' . $db->quoteName('s.rgt') . ')'
                    . ' OR (' . $db->quoteName('c.lft') . ' < ' . $db->quoteName('s.lft')
                    . ' AND ' . $db->quoteName('s.rgt') . ' < ' . $db->quoteName('c.rgt') . ')'
                );
            }
        } else {
            $query->from($db->quoteName('#__categories', 'c'));

            if ($app->isClient('site') && Multilanguage::isEnabled()) {
                $query->whereIn($db->quoteName('c.language'), [Factory::getLanguage()->getTag(), '*'], ParameterType::STRING);
            }
        }

        // Note: i for item
        if ($this->_options['countItems'] == 1) {
            $subQuery = $db->getQuery(true)
                ->select('COUNT(' . $db->quoteName($db->escape('i.' . $this->_key)) . ')')
                ->from($db->quoteName($db->escape($this->_table), 'i'))
                ->where($db->quoteName($db->escape('i.' . $this->_field)) . ' = ' . $db->quoteName('c.id'));

            if ($this->_options['published'] == 1) {
                $subQuery->where($db->quoteName($db->escape('i.' . $this->_statefield)) . ' = 1');
            }

            if ($this->_options['currentlang'] !== 0) {
                $subQuery->where(
                    $db->quoteName('i.language')
                    . ' IN (' . implode(',', $query->bindArray([$this->_options['currentlang'], '*'], ParameterType::STRING)) . ')'
                );
            }

            $query->select('(' . $subQuery . ') AS ' . $db->quoteName('numitems'));
        }

        // Get the results
        $db->setQuery($query);
        $results        = $db->loadObjectList('id');
        $childrenLoaded = false;

        if (\count($results)) {
            // Foreach categories
            foreach ($results as $result) {
                // Deal with root category
                if ($result->id == 1) {
                    $result->id = 'root';
                }

                // Deal with parent_id
                if ($result->parent_id == 1) {
                    $result->parent_id = 'root';
                }

                // Create the node
                if (!isset($this->_nodes[$result->id])) {
                    // Create the CategoryNode and add to _nodes
                    $this->_nodes[$result->id] = new CategoryNode($result, $this);

                    // If this is not root and if the current node's parent is in the list or the current node parent is 0
                    if ($result->id !== 'root' && (isset($this->_nodes[$result->parent_id]) || $result->parent_id == 1)) {
                        // Compute relationship between node and its parent - set the parent in the _nodes field
                        $this->_nodes[$result->id]->setParent($this->_nodes[$result->parent_id]);
                    }

                    // If the node's parent id is not in the _nodes list and the node is not root (doesn't have parent_id == 0),
                    // then remove the node from the list
                    if (!(isset($this->_nodes[$result->parent_id]) || $result->parent_id == 0)) {
                        unset($this->_nodes[$result->id]);
                        continue;
                    }

                    if ($result->id == $id || $childrenLoaded) {
                        $this->_nodes[$result->id]->setAllLoaded();
                        $childrenLoaded = true;
                    }
                } elseif ($result->id == $id || $childrenLoaded) {
                    // Create the CategoryNode
                    $this->_nodes[$result->id] = new CategoryNode($result, $this);

                    if ($result->id !== 'root' && (isset($this->_nodes[$result->parent_id]) || $result->parent_id)) {
                        // Compute relationship between node and its parent
                        $this->_nodes[$result->id]->setParent($this->_nodes[$result->parent_id]);
                    }

                    // If the node's parent id is not in the _nodes list and the node is not root (doesn't have parent_id == 0),
                    // then remove the node from the list
                    if (!(isset($this->_nodes[$result->parent_id]) || $result->parent_id == 0)) {
                        unset($this->_nodes[$result->id]);
                        continue;
                    }

                    if ($result->id == $id || $childrenLoaded) {
                        $this->_nodes[$result->id]->setAllLoaded();
                        $childrenLoaded = true;
                    }
                }
            }
        } else {
            $this->_nodes[$id] = null;
        }
    }
}
