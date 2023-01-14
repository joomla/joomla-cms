<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Indexer;

use Joomla\CMS\Factory;
use Joomla\CMS\Tree\NodeInterface;
use Joomla\Component\Finder\Administrator\Table\MapTable;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Taxonomy base class for the Finder indexer package.
 *
 * @since  2.5
 */
class Taxonomy
{
    /**
     * An internal cache of taxonomy data.
     *
     * @var    object[]
     * @since  4.0.0
     */
    public static $taxonomies = [];

    /**
     * An internal cache of branch data.
     *
     * @var    object[]
     * @since  4.0.0
     */
    public static $branches = [];

    /**
     * An internal cache of taxonomy node data for inserting it.
     *
     * @var    object[]
     * @since  2.5
     */
    public static $nodes = [];

    /**
     * Method to add a branch to the taxonomy tree.
     *
     * @param   string   $title   The title of the branch.
     * @param   integer  $state   The published state of the branch. [optional]
     * @param   integer  $access  The access state of the branch. [optional]
     *
     * @return  integer  The id of the branch.
     *
     * @since   2.5
     * @throws  \RuntimeException on database error.
     */
    public static function addBranch($title, $state = 1, $access = 1)
    {
        $node = new \stdClass();
        $node->title = $title;
        $node->state = $state;
        $node->access = $access;
        $node->parent_id = 1;
        $node->language = '';

        return self::storeNode($node, 1);
    }

    /**
     * Method to add a node to the taxonomy tree.
     *
     * @param   string   $branch    The title of the branch to store the node in.
     * @param   string   $title     The title of the node.
     * @param   integer  $state     The published state of the node. [optional]
     * @param   integer  $access    The access state of the node. [optional]
     * @param   string   $language  The language of the node. [optional]
     *
     * @return  integer  The id of the node.
     *
     * @since   2.5
     * @throws  \RuntimeException on database error.
     */
    public static function addNode($branch, $title, $state = 1, $access = 1, $language = '')
    {
        // Get the branch id, insert it if it does not exist.
        $branchId = static::addBranch($branch);

        $node = new \stdClass();
        $node->title = $title;
        $node->state = $state;
        $node->access = $access;
        $node->parent_id = $branchId;
        $node->language = $language;

        return self::storeNode($node, $branchId);
    }

    /**
     * Method to add a nested node to the taxonomy tree.
     *
     * @param   string         $branch    The title of the branch to store the node in.
     * @param   NodeInterface  $node      The source-node of the taxonomy node.
     * @param   integer        $state     The published state of the node. [optional]
     * @param   integer        $access    The access state of the node. [optional]
     * @param   string         $language  The language of the node. [optional]
     * @param   integer        $branchId  ID of a branch if known. [optional]
     *
     * @return  integer  The id of the node.
     *
     * @since   4.0.0
     */
    public static function addNestedNode($branch, NodeInterface $node, $state = 1, $access = 1, $language = '', $branchId = null)
    {
        if (!$branchId) {
            // Get the branch id, insert it if it does not exist.
            $branchId = static::addBranch($branch);
        }

        $parent = $node->getParent();

        if ($parent && $parent->title != 'ROOT') {
            $parentId = self::addNestedNode($branch, $parent, $state, $access, $language, $branchId);
        } else {
            $parentId = $branchId;
        }

        $temp = new \stdClass();
        $temp->title = $node->title;
        $temp->state = $state;
        $temp->access = $access;
        $temp->parent_id = $parentId;
        $temp->language = $language;

        return self::storeNode($temp, $parentId);
    }

    /**
     * A helper method to store a node in the taxonomy
     *
     * @param   object   $node      The node data to include
     * @param   integer  $parentId  The parent id of the node to add.
     *
     * @return  integer  The id of the inserted node.
     *
     * @since   4.0.0
     * @throws  \RuntimeException
     */
    protected static function storeNode($node, $parentId)
    {
        // Check to see if the node is in the cache.
        if (isset(static::$nodes[$parentId . ':' . $node->title])) {
            return static::$nodes[$parentId . ':' . $node->title]->id;
        }

        // Check to see if the node is in the table.
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__finder_taxonomy'))
            ->where($db->quoteName('parent_id') . ' = ' . $db->quote($parentId))
            ->where($db->quoteName('title') . ' = ' . $db->quote($node->title))
            ->where($db->quoteName('language') . ' = ' . $db->quote($node->language));

        $db->setQuery($query);

        // Get the result.
        $result = $db->loadObject();

        // Check if the database matches the input data.
        if ((bool) $result && $result->state == $node->state && $result->access == $node->access) {
            // The data matches, add the item to the cache.
            static::$nodes[$parentId . ':' . $node->title] = $result;

            return static::$nodes[$parentId . ':' . $node->title]->id;
        }

        /*
         * The database did not match the input. This could be because the
         * state has changed or because the node does not exist. Let's figure
         * out which case is true and deal with it.
         * @todo: use factory?
         */
        $nodeTable = new MapTable($db);

        if (empty($result)) {
            // Prepare the node object.
            $nodeTable->title = $node->title;
            $nodeTable->state = (int) $node->state;
            $nodeTable->access = (int) $node->access;
            $nodeTable->language = $node->language;
            $nodeTable->setLocation((int) $parentId, 'last-child');
        } else {
            // Prepare the node object.
            $nodeTable->id = (int) $result->id;
            $nodeTable->title = $result->title;
            $nodeTable->state = (int) ($node->state > 0 ? $node->state : $result->state);
            $nodeTable->access = (int) $result->access;
            $nodeTable->language = $node->language;
            $nodeTable->setLocation($result->parent_id, 'last-child');
        }

        // Check the data.
        if (!$nodeTable->check()) {
            $error = $nodeTable->getError();

            if ($error instanceof \Exception) {
                // \Joomla\CMS\Table\NestedTable sets errors of exceptions, so in this case we can pass on more
                // information
                throw new \RuntimeException(
                    $error->getMessage(),
                    $error->getCode(),
                    $error
                );
            }

            // Standard string returned. Probably from the \Joomla\CMS\Table\Table class
            throw new \RuntimeException($error, 500);
        }

        // Store the data.
        if (!$nodeTable->store()) {
            $error = $nodeTable->getError();

            if ($error instanceof \Exception) {
                // \Joomla\CMS\Table\NestedTable sets errors of exceptions, so in this case we can pass on more
                // information
                throw new \RuntimeException(
                    $error->getMessage(),
                    $error->getCode(),
                    $error
                );
            }

            // Standard string returned. Probably from the \Joomla\CMS\Table\Table class
            throw new \RuntimeException($error, 500);
        }

        $nodeTable->rebuildPath($nodeTable->id);

        // Add the node to the cache.
        static::$nodes[$parentId . ':' . $nodeTable->title] = (object) $nodeTable->getProperties();

        return static::$nodes[$parentId . ':' . $nodeTable->title]->id;
    }

    /**
     * Method to add a map entry between a link and a taxonomy node.
     *
     * @param   integer  $linkId  The link to map to.
     * @param   integer  $nodeId  The node to map to.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     * @throws  \RuntimeException on database error.
     */
    public static function addMap($linkId, $nodeId)
    {
        // Insert the map.
        $db = Factory::getDbo();

        $query = $db->getQuery(true)
            ->select($db->quoteName('link_id'))
            ->from($db->quoteName('#__finder_taxonomy_map'))
            ->where($db->quoteName('link_id') . ' = ' . (int) $linkId)
            ->where($db->quoteName('node_id') . ' = ' . (int) $nodeId);
        $db->setQuery($query);
        $db->execute();
        $id = (int) $db->loadResult();

        if (!$id) {
            $map = new \stdClass();
            $map->link_id = (int) $linkId;
            $map->node_id = (int) $nodeId;
            $db->insertObject('#__finder_taxonomy_map', $map);
        }

        return true;
    }

    /**
     * Method to get the title of all taxonomy branches.
     *
     * @return  array  An array of branch titles.
     *
     * @since   2.5
     * @throws  \RuntimeException on database error.
     */
    public static function getBranchTitles()
    {
        $db = Factory::getDbo();

        // Set user variables
        $groups = implode(',', Factory::getUser()->getAuthorisedViewLevels());

        // Create a query to get the taxonomy branch titles.
        $query = $db->getQuery(true)
            ->select($db->quoteName('title'))
            ->from($db->quoteName('#__finder_taxonomy'))
            ->where($db->quoteName('parent_id') . ' = 1')
            ->where($db->quoteName('state') . ' = 1')
            ->where($db->quoteName('access') . ' IN (' . $groups . ')');

        // Get the branch titles.
        $db->setQuery($query);

        return $db->loadColumn();
    }

    /**
     * Method to find a taxonomy node in a branch.
     *
     * @param   string  $branch  The branch to search.
     * @param   string  $title   The title of the node.
     *
     * @return  mixed  Integer id on success, null on no match.
     *
     * @since   2.5
     * @throws  \RuntimeException on database error.
     */
    public static function getNodeByTitle($branch, $title)
    {
        $db = Factory::getDbo();

        // Set user variables
        $groups = implode(',', Factory::getUser()->getAuthorisedViewLevels());

        // Create a query to get the node.
        $query = $db->getQuery(true)
            ->select('t1.*')
            ->from($db->quoteName('#__finder_taxonomy') . ' AS t1')
            ->join('INNER', $db->quoteName('#__finder_taxonomy') . ' AS t2 ON t2.id = t1.parent_id')
            ->where('t1.access IN (' . $groups . ')')
            ->where('t1.state = 1')
            ->where('t1.title LIKE ' . $db->quote($db->escape($title) . '%'))
            ->where('t2.access IN (' . $groups . ')')
            ->where('t2.state = 1')
            ->where('t2.title = ' . $db->quote($branch));

        // Get the node.
        $query->setLimit(1);
        $db->setQuery($query);

        return $db->loadObject();
    }

    /**
     * Method to remove map entries for a link.
     *
     * @param   integer  $linkId  The link to remove.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     * @throws  \RuntimeException on database error.
     */
    public static function removeMaps($linkId)
    {
        // Delete the maps.
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__finder_taxonomy_map'))
            ->where($db->quoteName('link_id') . ' = ' . (int) $linkId);
        $db->setQuery($query);
        $db->execute();

        return true;
    }

    /**
     * Method to remove orphaned taxonomy maps
     *
     * @return  integer  The number of deleted rows.
     *
     * @since   4.2.0
     * @throws  \RuntimeException on database error.
     */
    public static function removeOrphanMaps()
    {
        // Delete all orphaned maps
        $db = Factory::getDbo();
        $query2 = $db->getQuery(true)
            ->select($db->quoteName('link_id'))
            ->from($db->quoteName('#__finder_links'));
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__finder_taxonomy_map'))
            ->where($db->quoteName('link_id') . ' NOT IN (' . $query2 . ')');
        $db->setQuery($query);
        $db->execute();
        $count = $db->getAffectedRows();

        return $count;
    }

    /**
     * Method to remove orphaned taxonomy nodes and branches.
     *
     * @return  integer  The number of deleted rows.
     *
     * @since   2.5
     * @throws  \RuntimeException on database error.
     */
    public static function removeOrphanNodes()
    {
        // Delete all orphaned nodes.
        $affectedRows = 0;
        $db           = Factory::getDbo();
        $nodeTable    = new MapTable($db);
        $query        = $db->getQuery(true);

        $query->select($db->quoteName('t.id'))
            ->from($db->quoteName('#__finder_taxonomy', 't'))
            ->join('LEFT', $db->quoteName('#__finder_taxonomy_map', 'm') . ' ON ' . $db->quoteName('m.node_id') . '=' . $db->quoteName('t.id'))
            ->where($db->quoteName('t.parent_id') . ' > 1 ')
            ->where('t.lft + 1 = t.rgt')
            ->where($db->quoteName('m.link_id') . ' IS NULL');

        do {
            $db->setQuery($query);
            $nodes = $db->loadColumn();

            foreach ($nodes as $node) {
                $nodeTable->delete($node);
                $affectedRows++;
            }
        } while ($nodes);

        return $affectedRows;
    }

    /**
     * Get a taxonomy based on its id or all taxonomies
     *
     * @param   integer  $id  Id of the taxonomy
     *
     * @return  object|array  A taxonomy object or an array of all taxonomies
     *
     * @since   4.0.0
     */
    public static function getTaxonomy($id = 0)
    {
        if (!count(self::$taxonomies)) {
            $db    = Factory::getDbo();
            $query = $db->getQuery(true);

            $query->select(['id','parent_id','lft','rgt','level','path','title','alias','state','access','language'])
                ->from($db->quoteName('#__finder_taxonomy'))
                ->order($db->quoteName('lft'));

            $db->setQuery($query);
            self::$taxonomies = $db->loadObjectList('id');
        }

        if ($id == 0) {
            return self::$taxonomies;
        }

        if (isset(self::$taxonomies[$id])) {
            return self::$taxonomies[$id];
        }

        return false;
    }

    /**
     * Get a taxonomy branch object based on its title or all branches
     *
     * @param   string  $title  Title of the branch
     *
     * @return  object|array  The object with the branch data or an array of all branches
     *
     * @since   4.0.0
     */
    public static function getBranch($title = '')
    {
        if (!count(self::$branches)) {
            $taxonomies = self::getTaxonomy();

            foreach ($taxonomies as $t) {
                if ($t->level == 1) {
                    self::$branches[$t->title] = $t;
                }
            }
        }

        if ($title == '') {
            return self::$branches;
        }

        if (isset(self::$branches[$title])) {
            return self::$branches[$title];
        }

        return false;
    }
}
