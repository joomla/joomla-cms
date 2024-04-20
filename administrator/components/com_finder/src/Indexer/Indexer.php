<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Indexer;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Profiler\Profiler;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;
use Joomla\Filesystem\File;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Main indexer class for the Finder indexer package.
 *
 * The indexer class provides the core functionality of the Finder
 * search engine. It is responsible for adding and updating the
 * content links table; extracting and scoring tokens; and maintaining
 * all referential information for the content.
 *
 * Note: All exceptions thrown from within this class should be caught
 * by the controller.
 *
 * @since  2.5
 */
class Indexer
{
    /**
     * The title context identifier.
     *
     * @var    integer
     * @since  2.5
     */
    public const TITLE_CONTEXT = 1;

    /**
     * The text context identifier.
     *
     * @var    integer
     * @since  2.5
     */
    public const TEXT_CONTEXT = 2;

    /**
     * The meta context identifier.
     *
     * @var    integer
     * @since  2.5
     */
    public const META_CONTEXT = 3;

    /**
     * The path context identifier.
     *
     * @var    integer
     * @since  2.5
     */
    public const PATH_CONTEXT = 4;

    /**
     * The misc context identifier.
     *
     * @var    integer
     * @since  2.5
     */
    public const MISC_CONTEXT = 5;

    /**
     * The indexer state object.
     *
     * @var    CMSObject
     * @since  2.5
     */
    public static $state;

    /**
     * The indexer profiler object.
     *
     * @var    Profiler
     * @since  2.5
     */
    public static $profiler;

    /**
     * Database driver cache.
     *
     * @var    \Joomla\Database\DatabaseDriver
     * @since  3.8.0
     */
    protected $db;

    /**
     * Reusable Query Template. To be used with clone.
     *
     * @var    QueryInterface
     * @since  3.8.0
     */
    protected $addTokensToDbQueryTemplate;

    /**
     * Indexer constructor.
     *
     * @param  ?DatabaseInterface  $db  The database
     *
     * @since  3.8.0
     */
    public function __construct(?DatabaseInterface $db = null)
    {
        if ($db === null) {
            @trigger_error(sprintf('Database will be mandatory in 5.0.'), E_USER_DEPRECATED);
            $db = Factory::getContainer()->get(DatabaseInterface::class);
        }

        $this->db = $db;

        // Set up query template for addTokensToDb
        $this->addTokensToDbQueryTemplate = $db->getQuery(true)->insert($db->quoteName('#__finder_tokens'))
            ->columns(
                [
                    $db->quoteName('term'),
                    $db->quoteName('stem'),
                    $db->quoteName('common'),
                    $db->quoteName('phrase'),
                    $db->quoteName('weight'),
                    $db->quoteName('context'),
                    $db->quoteName('language'),
                ]
            );
    }

    /**
     * Method to get the indexer state.
     *
     * @return  CMSObject  The indexer state object.
     *
     * @since   2.5
     */
    public static function getState()
    {
        // First, try to load from the internal state.
        if ((bool) static::$state) {
            return static::$state;
        }

        // If we couldn't load from the internal state, try the session.
        $session = Factory::getSession();
        $data    = $session->get('_finder.state', null);

        // If the state is empty, load the values for the first time.
        if (empty($data)) {
            $data        = new CMSObject();
            $data->force = false;

            // Load the default configuration options.
            $data->options = ComponentHelper::getParams('com_finder');
            $db            = Factory::getDbo();

            if ($db->getServerType() == 'mysql') {
                /**
                 * Try to calculate the heapsize for the memory table for indexing. If this fails,
                 * we fall back on a reasonable small size. We want to prevent the system to fail
                 * and block saving content.
                 */
                try {
                    $db->setQuery('SHOW VARIABLES LIKE ' . $db->quote('max_heap_table_size'));
                    $heapsize = $db->loadObject();

                    /**
                     * In tests, the size of a row seems to have been around 720 bytes.
                     * We take 800 to be on the safe side.
                     */
                    $memory_table_limit = (int) ($heapsize->Value / 800);
                    $data->options->set('memory_table_limit', $memory_table_limit);
                } catch (\Exception $e) {
                    // Something failed. We fall back to a reasonable guess.
                    $data->options->set('memory_table_limit', 7500);
                }
            } else {
                // We are running on PostgreSQL and don't have this issue, so we set a rather high number.
                $data->options->set('memory_table_limit', 50000);
            }

            // Setup the weight lookup information.
            $data->weights = [
                self::TITLE_CONTEXT => round($data->options->get('title_multiplier', 1.7), 2),
                self::TEXT_CONTEXT  => round($data->options->get('text_multiplier', 0.7), 2),
                self::META_CONTEXT  => round($data->options->get('meta_multiplier', 1.2), 2),
                self::PATH_CONTEXT  => round($data->options->get('path_multiplier', 2.0), 2),
                self::MISC_CONTEXT  => round($data->options->get('misc_multiplier', 0.3), 2),
            ];

            // Set the current time as the start time.
            $data->startTime = Factory::getDate()->toSql();

            // Set the remaining default values.
            $data->batchSize   = (int) $data->options->get('batch_size', 50);
            $data->batchOffset = 0;
            $data->totalItems  = 0;
            $data->pluginState = [];
        }

        // Setup the profiler if debugging is enabled.
        if (Factory::getApplication()->get('debug')) {
            static::$profiler = Profiler::getInstance('FinderIndexer');
        }

        // Set the state.
        static::$state = $data;

        return static::$state;
    }

    /**
     * Method to set the indexer state.
     *
     * @param   CMSObject  $data  A new indexer state object.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   2.5
     */
    public static function setState($data)
    {
        // Check the state object.
        if (empty($data) || !$data instanceof CMSObject) {
            return false;
        }

        // Set the new internal state.
        static::$state = $data;

        // Set the new session state.
        Factory::getSession()->set('_finder.state', $data);

        return true;
    }

    /**
     * Method to reset the indexer state.
     *
     * @return  void
     *
     * @since   2.5
     */
    public static function resetState()
    {
        // Reset the internal state to null.
        self::$state = null;

        // Reset the session state to null.
        Factory::getSession()->set('_finder.state', null);
    }

    /**
     * Method to index a content item.
     *
     * @param   Result  $item    The content item to index.
     * @param   string  $format  The format of the content. [optional]
     *
     * @return  integer  The ID of the record in the links table.
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    public function index($item, $format = 'html')
    {
        // Mark beforeIndexing in the profiler.
        static::$profiler ? static::$profiler->mark('beforeIndexing') : null;
        $db         = $this->db;
        $serverType = strtolower($db->getServerType());

        // Check if the item is in the database.
        $query = $db->getQuery(true)
            ->select($db->quoteName('link_id') . ', ' . $db->quoteName('md5sum'))
            ->from($db->quoteName('#__finder_links'))
            ->where($db->quoteName('url') . ' = ' . $db->quote($item->url));

        // Load the item  from the database.
        $db->setQuery($query);
        $link = $db->loadObject();

        // Get the indexer state.
        $state = static::getState();

        // Get the signatures of the item.
        $curSig = static::getSignature($item);
        $oldSig = $link->md5sum ?? null;

        // Get the other item information.
        $linkId = empty($link->link_id) ? null : $link->link_id;
        $isNew  = empty($link->link_id);

        // Check the signatures. If they match, the item is up to date.
        if (!$isNew && $curSig == $oldSig) {
            return $linkId;
        }

        /*
         * If the link already exists, flush all the term maps for the item.
         * Maps are stored in 16 tables so we need to iterate through and flush
         * each table one at a time.
         */
        if (!$isNew) {
            // Flush the maps for the link.
            $query->clear()
                ->delete($db->quoteName('#__finder_links_terms'))
                ->where($db->quoteName('link_id') . ' = ' . (int) $linkId);
            $db->setQuery($query);
            $db->execute();

            // Remove the taxonomy maps.
            Taxonomy::removeMaps($linkId);
        }

        // Mark afterUnmapping in the profiler.
        static::$profiler ? static::$profiler->mark('afterUnmapping') : null;

        // Perform cleanup on the item data.
        $item->publish_start_date = (int) $item->publish_start_date != 0 ? $item->publish_start_date : null;
        $item->publish_end_date   = (int) $item->publish_end_date != 0 ? $item->publish_end_date : null;
        $item->start_date         = (int) $item->start_date != 0 ? $item->start_date : null;
        $item->end_date           = (int) $item->end_date != 0 ? $item->end_date : null;

        // Prepare the item description.
        $item->description = Helper::parse($item->summary ?? '');

        /*
         * Now, we need to enter the item into the links table. If the item
         * already exists in the database, we need to use an UPDATE query.
         * Otherwise, we need to use an INSERT to get the link id back.
         */
        $entry        = new \stdClass();
        $entry->url   = $item->url;
        $entry->route = $item->route;
        $entry->title = $item->title;

        // We are shortening the description in order to not run into length issues with this field
        $entry->description        = StringHelper::substr($item->description, 0, 32000);
        $entry->indexdate          = Factory::getDate()->toSql();
        $entry->state              = (int) $item->state;
        $entry->access             = (int) $item->access;
        $entry->language           = $item->language;
        $entry->type_id            = (int) $item->type_id;
        $entry->object             = '';
        $entry->publish_start_date = $item->publish_start_date;
        $entry->publish_end_date   = $item->publish_end_date;
        $entry->start_date         = $item->start_date;
        $entry->end_date           = $item->end_date;
        $entry->list_price         = (float) ($item->list_price ?: 0);
        $entry->sale_price         = (float) ($item->sale_price ?: 0);

        if ($isNew) {
            // Insert the link and get its id.
            $db->insertObject('#__finder_links', $entry);
            $linkId = (int) $db->insertid();
        } else {
            // Update the link.
            $entry->link_id = $linkId;
            $db->updateObject('#__finder_links', $entry, 'link_id');
        }

        // Set up the variables we will need during processing.
        $count = 0;

        // Mark afterLinking in the profiler.
        static::$profiler ? static::$profiler->mark('afterLinking') : null;

        // Truncate the tokens tables.
        $db->truncateTable('#__finder_tokens');

        // Truncate the tokens aggregate table.
        $db->truncateTable('#__finder_tokens_aggregate');

        /*
         * Process the item's content. The items can customize their
         * processing instructions to define extra properties to process
         * or rearrange how properties are weighted.
         */
        foreach ($item->getInstructions() as $group => $properties) {
            // Iterate through the properties of the group.
            foreach ($properties as $property) {
                // Check if the property exists in the item.
                if (empty($item->$property)) {
                    continue;
                }

                // Tokenize the property.
                if (\is_array($item->$property)) {
                    // Tokenize an array of content and add it to the database.
                    foreach ($item->$property as $ip) {
                        /*
                         * If the group is path, we need to a few extra processing
                         * steps to strip the extension and convert slashes and dashes
                         * to spaces.
                         */
                        if ($group === static::PATH_CONTEXT) {
                            $ip = File::stripExt($ip);
                            $ip = str_replace(['/', '-'], ' ', $ip);
                        }

                        // Tokenize a string of content and add it to the database.
                        $count += $this->tokenizeToDb($ip, $group, $item->language, $format, $count);

                        // Check if we're approaching the memory limit of the token table.
                        if ($count > static::$state->options->get('memory_table_limit', 7500)) {
                            $this->toggleTables(false);
                        }
                    }
                } else {
                    /*
                     * If the group is path, we need to a few extra processing
                     * steps to strip the extension and convert slashes and dashes
                     * to spaces.
                     */
                    if ($group === static::PATH_CONTEXT) {
                        $item->$property = File::stripExt($item->$property);
                        $item->$property = str_replace('/', ' ', $item->$property);
                        $item->$property = str_replace('-', ' ', $item->$property);
                    }

                    // Tokenize a string of content and add it to the database.
                    $count += $this->tokenizeToDb($item->$property, $group, $item->language, $format, $count);

                    // Check if we're approaching the memory limit of the token table.
                    if ($count > static::$state->options->get('memory_table_limit', 30000)) {
                        $this->toggleTables(false);
                    }
                }
            }
        }

        /*
         * Process the item's taxonomy. The items can customize their
         * taxonomy mappings to define extra properties to map.
         */
        foreach ($item->getTaxonomy() as $branch => $nodes) {
            // Iterate through the nodes and map them to the branch.
            foreach ($nodes as $node) {
                // Add the node to the tree.
                if ($node->nested) {
                    $nodeId = Taxonomy::addNestedNode($branch, $node->node, $node->state, $node->access, $node->language);
                } else {
                    $nodeId = Taxonomy::addNode($branch, $node->title, $node->state, $node->access, $node->language);
                }

                if (!$nodeId) {
                    continue;
                }

                // Add the link => node map.
                Taxonomy::addMap($linkId, $nodeId);
                $node->id = $nodeId;
            }
        }

        // Mark afterProcessing in the profiler.
        static::$profiler ? static::$profiler->mark('afterProcessing') : null;

        /*
         * At this point, all of the item's content has been parsed, tokenized
         * and inserted into the #__finder_tokens table. Now, we need to
         * aggregate all the data into that table into a more usable form. The
         * aggregated data will be inserted into #__finder_tokens_aggregate
         * table.
         */
        $query = 'INSERT INTO ' . $db->quoteName('#__finder_tokens_aggregate') .
            ' (' . $db->quoteName('term_id') .
            ', ' . $db->quoteName('term') .
            ', ' . $db->quoteName('stem') .
            ', ' . $db->quoteName('common') .
            ', ' . $db->quoteName('phrase') .
            ', ' . $db->quoteName('term_weight') .
            ', ' . $db->quoteName('context') .
            ', ' . $db->quoteName('context_weight') .
            ', ' . $db->quoteName('total_weight') .
            ', ' . $db->quoteName('language') . ')' .
            ' SELECT' .
            ' COALESCE(t.term_id, 0), t1.term, t1.stem, t1.common, t1.phrase, t1.weight, t1.context,' .
            ' ROUND( t1.weight * COUNT( t2.term ) * %F, 8 ) AS context_weight, 0, t1.language' .
            ' FROM (' .
            '   SELECT DISTINCT t1.term, t1.stem, t1.common, t1.phrase, t1.weight, t1.context, t1.language' .
            '   FROM ' . $db->quoteName('#__finder_tokens') . ' AS t1' .
            '   WHERE t1.context = %d' .
            ' ) AS t1' .
            ' JOIN ' . $db->quoteName('#__finder_tokens') . ' AS t2 ON t2.term = t1.term AND t2.language = t1.language' .
            ' LEFT JOIN ' . $db->quoteName('#__finder_terms') . ' AS t ON t.term = t1.term AND t.language = t1.language' .
            ' WHERE t2.context = %d' .
            ' GROUP BY t1.term, t.term_id, t1.term, t1.stem, t1.common, t1.phrase, t1.weight, t1.context, t1.language' .
            ' ORDER BY t1.term DESC';

        // Iterate through the contexts and aggregate the tokens per context.
        foreach ($state->weights as $context => $multiplier) {
            // Run the query to aggregate the tokens for this context..
            $db->setQuery(sprintf($query, $multiplier, $context, $context));
            $db->execute();
        }

        // Mark afterAggregating in the profiler.
        static::$profiler ? static::$profiler->mark('afterAggregating') : null;

        /*
         * When we pulled down all of the aggregate data, we did a LEFT JOIN
         * over the terms table to try to find all the term ids that
         * already exist for our tokens. If any of the rows in the aggregate
         * table have a term of 0, then no term record exists for that
         * term so we need to add it to the terms table.
         */
        $db->setQuery(
            'INSERT INTO ' . $db->quoteName('#__finder_terms') .
            ' (' . $db->quoteName('term') .
            ', ' . $db->quoteName('stem') .
            ', ' . $db->quoteName('common') .
            ', ' . $db->quoteName('phrase') .
            ', ' . $db->quoteName('weight') .
            ', ' . $db->quoteName('soundex') .
            ', ' . $db->quoteName('language') . ')' .
            ' SELECT ta.term, ta.stem, ta.common, ta.phrase, ta.term_weight, SOUNDEX(ta.term), ta.language' .
            ' FROM ' . $db->quoteName('#__finder_tokens_aggregate') . ' AS ta' .
            ' WHERE ta.term_id = 0' .
            ' GROUP BY ta.term, ta.stem, ta.common, ta.phrase, ta.term_weight, SOUNDEX(ta.term), ta.language'
        );
        $db->execute();

        /*
         * Now, we just inserted a bunch of new records into the terms table
         * so we need to go back and update the aggregate table with all the
         * new term ids.
         */
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__finder_tokens_aggregate', 'ta'))
            ->innerJoin($db->quoteName('#__finder_terms', 't'), 't.term = ta.term AND t.language = ta.language')
            ->where('ta.term_id = 0');

        if ($serverType == 'mysql') {
            $query->set($db->quoteName('ta.term_id') . ' = ' . $db->quoteName('t.term_id'));
        } else {
            $query->set($db->quoteName('term_id') . ' = ' . $db->quoteName('t.term_id'));
        }

        $db->setQuery($query);
        $db->execute();

        // Mark afterTerms in the profiler.
        static::$profiler ? static::$profiler->mark('afterTerms') : null;

        /*
         * After we've made sure that all of the terms are in the terms table
         * and the aggregate table has the correct term ids, we need to update
         * the links counter for each term by one.
         */
        $query->clear()
            ->update($db->quoteName('#__finder_terms', 't'))
            ->innerJoin($db->quoteName('#__finder_tokens_aggregate', 'ta'), 'ta.term_id = t.term_id');

        if ($serverType == 'mysql') {
            $query->set($db->quoteName('t.links') . ' = t.links + 1');
        } else {
            $query->set($db->quoteName('links') . ' = t.links + 1');
        }

        $db->setQuery($query);
        $db->execute();

        // Mark afterTerms in the profiler.
        static::$profiler ? static::$profiler->mark('afterTerms') : null;

        /*
         * At this point, the aggregate table contains a record for each
         * term in each context. So, we're going to pull down all of that
         * data while grouping the records by term and add all of the
         * sub-totals together to arrive at the final total for each token for
         * this link. Then, we insert all of that data into the mapping table.
         */
        $db->setQuery(
            'INSERT INTO ' . $db->quoteName('#__finder_links_terms') .
            ' (' . $db->quoteName('link_id') .
            ', ' . $db->quoteName('term_id') .
            ', ' . $db->quoteName('weight') . ')' .
            ' SELECT ' . (int) $linkId . ', ' . $db->quoteName('term_id') . ',' .
            ' ROUND(SUM(' . $db->quoteName('context_weight') . '), 8)' .
            ' FROM ' . $db->quoteName('#__finder_tokens_aggregate') .
            ' GROUP BY ' . $db->quoteName('term') . ', ' . $db->quoteName('term_id') .
            ' ORDER BY ' . $db->quoteName('term') . ' DESC'
        );
        $db->execute();

        // Mark afterMapping in the profiler.
        static::$profiler ? static::$profiler->mark('afterMapping') : null;

        // Update the signature.
        $object = serialize($item);
        $query->clear()
            ->update($db->quoteName('#__finder_links'))
            ->set($db->quoteName('md5sum') . ' = :md5sum')
            ->set($db->quoteName('object') . ' = :object')
            ->where($db->quoteName('link_id') . ' = :linkid')
            ->bind(':md5sum', $curSig)
            ->bind(':object', $object, ParameterType::LARGE_OBJECT)
            ->bind(':linkid', $linkId, ParameterType::INTEGER);
        $db->setQuery($query);
        $db->execute();

        // Mark afterSigning in the profiler.
        static::$profiler ? static::$profiler->mark('afterSigning') : null;

        // Truncate the tokens tables.
        $db->truncateTable('#__finder_tokens');

        // Truncate the tokens aggregate table.
        $db->truncateTable('#__finder_tokens_aggregate');

        // Toggle the token tables back to memory tables.
        $this->toggleTables(true);

        // Mark afterTruncating in the profiler.
        static::$profiler ? static::$profiler->mark('afterTruncating') : null;

        // Trigger a plugin event after indexing
        PluginHelper::importPlugin('finder');
        Factory::getApplication()->triggerEvent('onFinderIndexAfterIndex', [$item, $linkId]);

        return $linkId;
    }

    /**
     * Method to remove a link from the index.
     *
     * @param   integer  $linkId            The id of the link.
     * @param   bool     $removeTaxonomies  Remove empty taxonomies
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    public function remove($linkId, $removeTaxonomies = true)
    {
        $db     = $this->db;
        $query  = $db->getQuery(true);
        $linkId = (int) $linkId;

        // Update the link counts for the terms.
        $query->clear()
            ->update($db->quoteName('#__finder_terms', 't'))
            ->join('INNER', $db->quoteName('#__finder_links_terms', 'm'), $db->quoteName('m.term_id') . ' = ' . $db->quoteName('t.term_id'))
            ->set($db->quoteName('links') . ' = ' . $db->quoteName('links') . ' - 1')
            ->where($db->quoteName('m.link_id') . ' = :linkid')
            ->bind(':linkid', $linkId, ParameterType::INTEGER);
        $db->setQuery($query)->execute();

        // Remove all records from the mapping tables.
        $query->clear()
            ->delete($db->quoteName('#__finder_links_terms'))
            ->where($db->quoteName('link_id') . ' = :linkid')
            ->bind(':linkid', $linkId, ParameterType::INTEGER);
        $db->setQuery($query)->execute();

        // Delete all orphaned terms.
        $query->clear()
            ->delete($db->quoteName('#__finder_terms'))
            ->where($db->quoteName('links') . ' <= 0');
        $db->setQuery($query)->execute();

        // Delete the link from the index.
        $query->clear()
            ->delete($db->quoteName('#__finder_links'))
            ->where($db->quoteName('link_id') . ' = :linkid')
            ->bind(':linkid', $linkId, ParameterType::INTEGER);
        $db->setQuery($query)->execute();

        // Remove the taxonomy maps.
        Taxonomy::removeMaps($linkId);

        // Remove the orphaned taxonomy nodes.
        if ($removeTaxonomies) {
            Taxonomy::removeOrphanNodes();
        }

        PluginHelper::importPlugin('finder');
        Factory::getApplication()->triggerEvent('onFinderIndexAfterDelete', [$linkId]);

        return true;
    }

    /**
     * Method to optimize the index. We use this method to remove unused terms
     * and any other optimizations that might be necessary.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    public function optimize()
    {
        // Get the database object.
        $db         = $this->db;
        $serverType = strtolower($db->getServerType());
        $query      = $db->getQuery(true);

        // Delete all orphaned terms.
        $query->delete($db->quoteName('#__finder_terms'))
            ->where($db->quoteName('links') . ' <= 0');
        $db->setQuery($query);
        $db->execute();

        // Delete all broken links. (Links missing the object)
        $query = $db->getQuery(true)
            ->delete('#__finder_links')
            ->where($db->quoteName('object') . ' = ' . $db->quote(''));
        $db->setQuery($query);
        $db->execute();

        // Delete all orphaned mappings of terms to links
        $query2 = $db->getQuery(true)
            ->select($db->quoteName('link_id'))
            ->from($db->quoteName('#__finder_links'));
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__finder_links_terms'))
            ->where($db->quoteName('link_id') . ' NOT IN (' . $query2 . ')');
        $db->setQuery($query);
        $db->execute();

        // Delete all orphaned terms
        $query2 = $db->getQuery(true)
            ->select($db->quoteName('term_id'))
            ->from($db->quoteName('#__finder_links_terms'));
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__finder_terms'))
            ->where($db->quoteName('term_id') . ' NOT IN (' . $query2 . ')');
        $db->setQuery($query);
        $db->execute();

        // Delete all orphaned taxonomies
        Taxonomy::removeOrphanMaps();
        Taxonomy::removeOrphanNodes();

        // Optimize the tables.
        $tables = [
            '#__finder_links',
            '#__finder_links_terms',
            '#__finder_filters',
            '#__finder_terms_common',
            '#__finder_types',
            '#__finder_taxonomy_map',
            '#__finder_taxonomy',
        ];

        foreach ($tables as $table) {
            if ($serverType == 'mysql') {
                $db->setQuery('OPTIMIZE TABLE ' . $db->quoteName($table));
                $db->execute();
            } else {
                $db->setQuery('VACUUM ' . $db->quoteName($table));
                $db->execute();
                $db->setQuery('REINDEX TABLE ' . $db->quoteName($table));
                $db->execute();
            }
        }

        return true;
    }

    /**
     * Method to get a content item's signature.
     *
     * @param   Result  $item  The content item to index.
     *
     * @return  string  The content item's signature.
     *
     * @since   2.5
     */
    protected static function getSignature($item)
    {
        // Get the indexer state.
        $state = static::getState();

        // Get the relevant configuration variables.
        $config = [
            $state->weights,
            $state->options->get('tuplecount', 1),
            $state->options->get('language_default', ''),
        ];

        return md5(serialize([$item, $config]));
    }

    /**
     * Method to parse input, tokenize it, and then add it to the database.
     *
     * @param   string|resource  $input    String or resource to use as input. A resource input will automatically be chunked to conserve
     *                                     memory. Strings will be chunked if longer than 2K in size.
     * @param   integer          $context  The context of the input. See context constants.
     * @param   string           $lang     The language of the input.
     * @param   string           $format   The format of the input.
     * @param   integer          $count    Number of terms indexed so far.
     *
     * @return  integer  The number of tokens extracted from the input.
     *
     * @since   2.5
     */
    protected function tokenizeToDb($input, $context, $lang, $format, $count = 0)
    {
        $buffer = null;

        if (empty($input)) {
            return $count;
        }

        // If the input is a resource, batch the process out.
        if (\is_resource($input)) {
            // Batch the process out to avoid memory limits.
            while (!feof($input)) {
                // Read into the buffer.
                $buffer .= fread($input, 2048);

                /*
                 * If we haven't reached the end of the file, seek to the last
                 * space character and drop whatever is after that to make sure
                 * we didn't truncate a term while reading the input.
                 */
                if (!feof($input)) {
                    // Find the last space character.
                    $ls = strrpos($buffer, ' ');

                    // Adjust string based on the last space character.
                    if ($ls) {
                        // Truncate the string to the last space character.
                        $string = substr($buffer, 0, $ls);

                        // Adjust the buffer based on the last space for the next iteration and trim.
                        $buffer = StringHelper::trim(substr($buffer, $ls));
                    } else {
                        // No space character was found.
                        $string = $buffer;
                    }
                } else {
                    // We've reached the end of the file, so parse whatever remains.
                    $string = $buffer;
                }

                // Parse, tokenise and add tokens to the database.
                $count = $this->tokenizeToDbShort($string, $context, $lang, $format, $count);

                unset($string);
            }

            return $count;
        }

        // Parse, tokenise and add tokens to the database.
        $count = $this->tokenizeToDbShort($input, $context, $lang, $format, $count);

        return $count;
    }

    /**
     * Method to parse input, tokenise it, then add the tokens to the database.
     *
     * @param   string   $input    String to parse, tokenise and add to database.
     * @param   integer  $context  The context of the input. See context constants.
     * @param   string   $lang     The language of the input.
     * @param   string   $format   The format of the input.
     * @param   integer  $count    The number of tokens processed so far.
     *
     * @return  integer  Cumulative number of tokens extracted from the input so far.
     *
     * @since   3.7.0
     */
    private function tokenizeToDbShort($input, $context, $lang, $format, $count)
    {
        static $filterCommon, $filterNumeric;

        if (\is_null($filterCommon)) {
            $params        = ComponentHelper::getParams('com_finder');
            $filterCommon  = $params->get('filter_commonwords', false);
            $filterNumeric = $params->get('filter_numerics', false);
        }

        // Parse the input.
        $input = Helper::parse($input, $format);

        // Check the input.
        if (empty($input)) {
            return $count;
        }

        // Tokenize the input.
        $tokens = Helper::tokenize($input, $lang);

        if (\count($tokens) == 0) {
            return $count;
        }

        $query = clone $this->addTokensToDbQueryTemplate;

        // Break into chunks of no more than 128 items
        $chunks = array_chunk($tokens, 128);

        foreach ($chunks as $tokens) {
            $query->clear('values');

            foreach ($tokens as $token) {
                // Database size for a term field
                if ($token->length > 75) {
                    continue;
                }

                if ($filterCommon && $token->common) {
                    continue;
                }

                if ($filterNumeric && $token->numeric) {
                    continue;
                }

                $query->values(
                    $this->db->quote($token->term) . ', '
                    . $this->db->quote($token->stem) . ', '
                    . (int) $token->common . ', '
                    . (int) $token->phrase . ', '
                    . $this->db->quote($token->weight) . ', '
                    . (int) $context . ', '
                    . $this->db->quote($token->language)
                );
                $count++;
            }

            // Check if we're approaching the memory limit of the token table.
            if ($count > static::$state->options->get('memory_table_limit', 7500)) {
                $this->toggleTables(false);
            }

            // Only execute the query if there are tokens to insert
            if ($query->values !== null) {
                $this->db->setQuery($query)->execute();
            }
        }

        return $count;
    }

    /**
     * Method to switch the token tables from Memory tables to Disk tables
     * when they are close to running out of memory.
     * Since this is not supported/implemented in all DB-drivers, the default is a stub method, which simply returns true.
     *
     * @param   boolean  $memory  Flag to control how they should be toggled.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    protected function toggleTables($memory)
    {
        static $supported = true;

        if (!$supported) {
            return true;
        }

        if (strtolower($this->db->getServerType()) != 'mysql') {
            $supported = false;

            return true;
        }

        static $state;

        // Get the database adapter.
        $db = $this->db;

        // Check if we are setting the tables to the Memory engine.
        if ($memory === true && $state !== true) {
            try {
                // Set the tokens table to Memory.
                $db->setQuery('ALTER TABLE ' . $db->quoteName('#__finder_tokens') . ' ENGINE = MEMORY');
                $db->execute();

                // Set the tokens aggregate table to Memory.
                $db->setQuery('ALTER TABLE ' . $db->quoteName('#__finder_tokens_aggregate') . ' ENGINE = MEMORY');
                $db->execute();
            } catch (\RuntimeException $e) {
                $supported = false;

                return true;
            }

            // Set the internal state.
            $state = $memory;
        } elseif ($memory === false && $state !== false) {
            // We must be setting the tables to the InnoDB engine.
            // Set the tokens table to InnoDB.
            $db->setQuery('ALTER TABLE ' . $db->quoteName('#__finder_tokens') . ' ENGINE = INNODB');
            $db->execute();

            // Set the tokens aggregate table to InnoDB.
            $db->setQuery('ALTER TABLE ' . $db->quoteName('#__finder_tokens_aggregate') . ' ENGINE = INNODB');
            $db->execute();

            // Set the internal state.
            $state = $memory;
        }

        return true;
    }
}
