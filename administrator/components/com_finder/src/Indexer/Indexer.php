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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Profiler\Profiler;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
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
     * @var    \stdClass
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
     * Offsets of the fields inside a single entry of $tokenAggregate. Packed arrays are used
     * instead of associative ones because this map holds one entry per distinct term of the
     * item and the hash tables of associative arrays would roughly double its size.
     *
     * @since  __DEPLOY_VERSION__
     */
    private const AGG_STEM   = 0;
    private const AGG_COMMON = 1;
    private const AGG_PHRASE = 2;
    private const AGG_WEIGHT = 3;
    private const AGG_COUNTS = 4;

    /**
     * The tokens of the item which is currently being indexed, aggregated by language, term
     * and context. This replaces the #__finder_tokens scratch table: the tokens are counted
     * in memory while they are produced, so the raw token stream never has to be written to
     * the database only to be grouped and read back.
     *
     * The structure is [language][term] => [stem, common, phrase, weight, [context => count]].
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    private $tokenAggregate = [];

    /**
     * The number of distinct terms currently held in $tokenAggregate. Tracked separately
     * because counting a nested array on every token would be needlessly expensive.
     *
     * @var    integer
     * @since  __DEPLOY_VERSION__
     */
    private $tokenAggregateSize = 0;

    /**
     * The resolved term ids of the item which is currently being indexed, mapped to their
     * accumulated weight. This replaces the #__finder_tokens_aggregate scratch table.
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    private $linkTerms = [];

    /**
     * The number of distinct terms after which $tokenAggregate is resolved to term ids and
     * released. A resolved entry costs roughly a tenth of an unresolved one, so this keeps
     * the memory usage of pathological items bounded without changing the result.
     *
     * Items only reach this in unusual setups, most notably a large document combined with
     * phrase indexing (tuplecount = 3), where nearly every n-gram is distinct.
     *
     * @var    integer
     * @since  __DEPLOY_VERSION__
     */
    protected $aggregateFlushThreshold = 50000;

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
            @trigger_error('Database will be mandatory in 7.0.', E_USER_DEPRECATED);
            $db = Factory::getContainer()->get(DatabaseInterface::class);
        }

        $this->db = $db;
    }

    /**
     * Method to get the indexer state.
     *
     * @return  \stdClass  The indexer state object.
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
        $session = Factory::getApplication()->getSession();
        $data    = $session->get('_finder.state', null);

        // If the state is empty, load the values for the first time.
        if (empty($data)) {
            $data        = new \stdClass();
            $data->force = false;

            // Load the default configuration options.
            $data->options = ComponentHelper::getParams('com_finder');

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
     * @param   \stdClass  $data  A new indexer state object.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   2.5
     */
    public static function setState($data)
    {
        // Check the state object.
        if (empty($data) || !$data instanceof \stdClass) {
            return false;
        }

        // Set the new internal state.
        static::$state = $data;

        // Set the new session state.
        Factory::getApplication()->getSession()->set('_finder.state', $data);

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
        Factory::getApplication()->getSession()->set('_finder.state', null);
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
        $db = $this->db;

        // Check if the item is in the database.
        $query = $db->createQuery()
            ->select($db->quoteName('link_id') . ', ' . $db->quoteName('md5sum'))
            ->from($db->quoteName('#__finder_links'))
            ->where($db->quoteName('url') . ' = ' . $db->quote($item->url));

        // Load the item  from the database.
        $db->setQuery($query);
        $link = $db->loadObject();

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
            $db->updateObject('#__finder_links', $entry, 'link_id', true);
        }

        // Set up the variables we will need during processing.
        $count = 0;

        // Mark afterLinking in the profiler.
        static::$profiler ? static::$profiler->mark('afterLinking') : null;

        // Drop whatever a previous item left behind.
        $this->resetTokens();

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
                        $count = $this->tokenizeToDb($ip, $group, $item->language, $format, $count);
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
                    $count = $this->tokenizeToDb($item->$property, $group, $item->language, $format, $count);
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
         * At this point all of the item's content has been parsed, tokenized and
         * aggregated in memory. Now the terms have to be resolved against the terms
         * table and mapped to the link.
         */
        $this->storeTokens($linkId);

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

        // Release the aggregated tokens of this item.
        $this->resetTokens();

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
        $query  = $db->createQuery();
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

        // Delete all broken links. (Links missing the object)
        $query = $db->createQuery()
            ->delete('#__finder_links')
            ->where($db->quoteName('object') . ' = ' . $db->quote(''));
        $db->setQuery($query);
        $db->execute();

        // Delete all orphaned mappings of terms to links
        $query2 = $db->createQuery()
            ->select($db->quoteName('link_id'))
            ->from($db->quoteName('#__finder_links'));
        $query = $db->createQuery()
            ->delete($db->quoteName('#__finder_links_terms'))
            ->where($db->quoteName('link_id') . ' NOT IN (' . $query2 . ')');
        $db->setQuery($query);
        $db->execute();

        // Update count of links in terms table
        $query  = $db->createQuery();
        $query2 = $db->createQuery();
        $query2->select('COUNT(lt.link_id)')
            ->from($db->quoteName('#__finder_links_terms', 'lt'))
            ->where($db->quoteName('lt.term_id') . ' = ' . $db->quoteName('t.term_id'));
        if ($serverType === 'mysql') {
            $query->update($db->quoteName('#__finder_terms', 't'))
                ->set($db->quoteName('t.links') . ' = (' . $query2 . ')');
        } else {
            $query->update($db->quoteName('#__finder_terms', 't'))
                ->set($db->quoteName('links') . ' = (' . $query2 . ')');
        }
        $db->setQuery($query);
        $db->execute();

        // Delete all orphaned terms.
        $query = $db->createQuery();
        $query->delete($db->quoteName('#__finder_terms'))
            ->where($db->quoteName('links') . ' <= 0');
        $db->setQuery($query);
        $db->execute();


        // Delete all orphaned terms
        $query2 = $db->createQuery()
            ->select($db->quoteName('term_id'))
            ->from($db->quoteName('#__finder_links_terms'));
        $query = $db->createQuery()
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

                // Parse, tokenise and aggregate the chunk.
                $count = $this->tokenizeChunk($string, $context, $lang, $format, $count);

                unset($string);
            }

            return $count;
        }

        // Parse, tokenise and aggregate the input.
        $count = $this->tokenizeChunk($input, $context, $lang, $format, $count);

        return $count;
    }

    /**
     * Method to parse input, tokenise it, then aggregate the tokens in memory.
     *
     * @param   string   $input    String to parse, tokenise and aggregate.
     * @param   integer  $context  The context of the input. See context constants.
     * @param   string   $lang     The language of the input.
     * @param   string   $format   The format of the input.
     * @param   integer  $count    The number of tokens processed so far.
     *
     * @return  integer  Cumulative number of tokens extracted from the input so far.
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function tokenizeChunk($input, $context, $lang, $format, $count)
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

        $context = (int) $context;

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

            /*
             * The language of a token is not necessarily the language of the item: on a site
             * which is not multilingual every token gets the '*' of the default language.
             */
            $language = $token->language;
            $term     = $token->term;

            /*
             * The counter is incremented in place. Reading the counters out, changing them
             * and writing them back would copy the array on every single token, and this is
             * the hottest loop of the whole indexer.
             */
            if (isset($this->tokenAggregate[$language][$term][self::AGG_COUNTS][$context])) {
                $this->tokenAggregate[$language][$term][self::AGG_COUNTS][$context]++;
            } elseif (isset($this->tokenAggregate[$language][$term])) {
                $this->tokenAggregate[$language][$term][self::AGG_COUNTS][$context] = 1;
            } else {
                $this->tokenAggregate[$language][$term] = [
                    self::AGG_STEM   => $token->stem,
                    self::AGG_COMMON => (int) $token->common,
                    self::AGG_PHRASE => (int) $token->phrase,
                    self::AGG_WEIGHT => (float) $token->weight,
                    self::AGG_COUNTS => [$context => 1],
                ];

                $this->tokenAggregateSize++;
            }

            $count++;
        }

        // Keep the memory usage of pathologically large items bounded.
        if ($this->tokenAggregateSize >= $this->aggregateFlushThreshold) {
            $this->flushTokenAggregate();
        }

        return $count;
    }

    /**
     * Method to discard everything that has been aggregated for the previous item.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function resetTokens()
    {
        $this->tokenAggregate     = [];
        $this->tokenAggregateSize = 0;
        $this->linkTerms          = [];
    }

    /**
     * Method to write the aggregated tokens of the current item to the index.
     *
     * @param   integer  $linkId  The id of the link the terms belong to.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Exception on database error.
     */
    protected function storeTokens($linkId)
    {
        // Resolve whatever is still unresolved.
        $this->flushTokenAggregate();

        if (!$this->linkTerms) {
            return;
        }

        $db     = $this->db;
        $linkId = (int) $linkId;

        // Mark afterAggregating in the profiler.
        static::$profiler ? static::$profiler->mark('afterAggregating') : null;

        // Bump the link counter of every term this link uses.
        foreach (array_chunk(array_keys($this->linkTerms), 1000) as $chunk) {
            $query = $db->createQuery()
                ->update($db->quoteName('#__finder_terms'))
                ->set($db->quoteName('links') . ' = ' . $db->quoteName('links') . ' + 1')
                ->whereIn($db->quoteName('term_id'), $chunk);
            $db->setQuery($query)->execute();
        }

        // Mark afterTerms in the profiler.
        static::$profiler ? static::$profiler->mark('afterTerms') : null;

        $insert = 'INSERT INTO ' . $db->quoteName('#__finder_links_terms')
            . ' (' . $db->quoteName('link_id')
            . ', ' . $db->quoteName('term_id')
            . ', ' . $db->quoteName('weight') . ') VALUES ';

        // Map the terms to the link.
        foreach (array_chunk($this->linkTerms, 1000, true) as $chunk) {
            $values = [];

            foreach ($chunk as $termId => $weight) {
                // %F instead of %f, the latter would use the decimal separator of the locale.
                $values[] = $linkId . ', ' . (int) $termId . ', ' . \sprintf('%.8F', $weight);
            }

            $db->setQuery($insert . '(' . implode('), (', $values) . ')')->execute();
        }

        $this->linkTerms = [];
    }

    /**
     * Method to resolve the currently aggregated terms to term ids and fold their weights
     * into the link => term map.
     *
     * A resolved entry is an integer key with a float value, while an unresolved one carries
     * the term, its stem and the per context counters. Releasing the aggregate here is what
     * allows tokenizeChunk() to cap its memory usage without changing the outcome.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Exception on database error.
     */
    private function flushTokenAggregate()
    {
        if (!$this->tokenAggregate) {
            return;
        }

        $state = static::getState();

        foreach ($this->tokenAggregate as $language => $terms) {
            $termIds = $this->resolveTermIds((string) $language, $terms);

            foreach ($terms as $term => $data) {
                /*
                 * PHP casts numeric array keys to integers, so the key has to be turned back
                 * into a string before it is looked up in the result of the database.
                 */
                $termId = $termIds[(string) $term] ?? null;

                /*
                 * A term can be missing here when another request removed it as an orphan
                 * between us creating it and reading it back. Skipping it costs this one
                 * document one term until it is indexed again, whereas aborting would cost
                 * the whole document, so this deliberately does not throw.
                 */
                if ($termId === null) {
                    continue;
                }

                $termId = (int) $termId;
                $weight = 0.0;

                /*
                 * The weight of a term in a context is its own weight, multiplied by the
                 * number of occurrences and by the multiplier configured for that context.
                 * The total weight is the sum over all contexts.
                 */
                foreach ($data[self::AGG_COUNTS] as $context => $occurrences) {
                    $multiplier = $state->weights[$context] ?? 0;
                    $weight += round($data[self::AGG_WEIGHT] * $occurrences * $multiplier, 8);
                }

                $this->linkTerms[$termId] = ($this->linkTerms[$termId] ?? 0.0) + $weight;
            }
        }

        $this->tokenAggregate     = [];
        $this->tokenAggregateSize = 0;
    }

    /**
     * Method to look up the term ids for a set of terms, creating the ones that do not exist
     * in the terms table yet.
     *
     * @param   string  $language  The language the terms belong to.
     * @param   array   $terms     The aggregated terms, keyed by term.
     *
     * @return  array  The term ids, keyed by term.
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Exception on database error.
     */
    private function resolveTermIds($language, array $terms)
    {
        $result = [];

        // Find the terms which the index already knows.
        foreach (array_chunk(array_keys($terms), 500) as $chunk) {
            $result += $this->loadTermIds($language, $chunk);
        }

        $missing = array_diff_key($terms, $result);

        if (!$missing) {
            return $result;
        }

        // Create the ones which are new.
        foreach (array_chunk($missing, 500, true) as $chunk) {
            $this->createTerms($language, $chunk);
        }

        /*
         * The ids are read back instead of being derived from the insert id: with
         * innodb_autoinc_lock_mode = 2 the generated ids are not guaranteed to be
         * consecutive. Reading them back also picks up the rows that a concurrent
         * request created in the meantime.
         */
        foreach (array_chunk(array_keys($missing), 500) as $chunk) {
            $result += $this->loadTermIds($language, $chunk);
        }

        return $result;
    }

    /**
     * Method to load the ids of the given terms from the terms table.
     *
     * @param   string  $language  The language the terms belong to.
     * @param   array   $terms     The terms to look up.
     *
     * @return  array  The term ids, keyed by term.
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Exception on database error.
     */
    private function loadTermIds($language, array $terms)
    {
        $db    = $this->db;
        $query = $db->createQuery()
            ->select($db->quoteName(['term', 'term_id']))
            ->from($db->quoteName('#__finder_terms'))
            ->where($db->quoteName('language') . ' = :language')
            ->bind(':language', $language)
            ->whereIn($db->quoteName('term'), array_map('strval', $terms), ParameterType::STRING);

        return $db->setQuery($query)->loadAssocList('term', 'term_id') ?: [];
    }

    /**
     * Method to add new terms to the terms table.
     *
     * The soundex is computed by the database instead of PHP, because PHP and the database
     * do not agree on the result and the search side computes it with SQL as well.
     *
     * Terms that a concurrent request created in the meantime are ignored rather than
     * aborting the insert, the caller reads the ids back afterwards either way.
     *
     * @param   string  $language  The language the terms belong to.
     * @param   array   $terms     The aggregated terms to create, keyed by term.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \Exception on database error.
     */
    private function createTerms($language, array $terms)
    {
        $db     = $this->db;
        $values = [];

        foreach ($terms as $term => $data) {
            $quoted   = $db->quote((string) $term);
            $values[] = $quoted . ', '
                . $db->quote($data[self::AGG_STEM]) . ', '
                . $data[self::AGG_COMMON] . ', '
                . $data[self::AGG_PHRASE] . ', '
                . \sprintf('%.8F', $data[self::AGG_WEIGHT]) . ', '
                . 'SOUNDEX(' . $quoted . '), '
                . $db->quote($language);
        }

        $columns = $db->quoteName('term')
            . ', ' . $db->quoteName('stem')
            . ', ' . $db->quoteName('common')
            . ', ' . $db->quoteName('phrase')
            . ', ' . $db->quoteName('weight')
            . ', ' . $db->quoteName('soundex')
            . ', ' . $db->quoteName('language');

        if (strtolower($db->getServerType()) === 'postgresql') {
            $sql = 'INSERT INTO ' . $db->quoteName('#__finder_terms') . ' (' . $columns . ')'
                . ' VALUES (' . implode('), (', $values) . ')'
                . ' ON CONFLICT (' . $db->quoteName('term') . ', ' . $db->quoteName('language') . ') DO NOTHING';
        } else {
            $sql = 'INSERT IGNORE INTO ' . $db->quoteName('#__finder_terms') . ' (' . $columns . ')'
                . ' VALUES (' . implode('), (', $values) . ')';
        }

        $db->setQuery($sql)->execute();
    }

    /**
     * Method to switch the token tables from Memory tables to Disk tables
     * when they are close to running out of memory.
     *
     * @param   boolean  $memory  Flag to control how they should be toggled.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     *
     * @deprecated  __DEPLOY_VERSION__ will be removed in 8.0
     *              The indexer aggregates the tokens in PHP and no longer uses scratch
     *              tables, so there is nothing left to toggle.
     */
    protected function toggleTables($memory)
    {
        return true;
    }
}
