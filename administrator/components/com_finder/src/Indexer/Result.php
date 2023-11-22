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
use Joomla\CMS\Tree\ImmutableNodeInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Result class for the Finder indexer package.
 *
 * This class uses magic __get() and __set() methods to prevent properties
 * being added that might confuse the system. All properties not explicitly
 * declared will be pushed into the elements array and can be accessed
 * explicitly using the getElement() method.
 *
 * @since  2.5
 */
class Result implements \Serializable
{
    /**
     * An array of extra result properties.
     *
     * @var    array
     * @since  2.5
     */
    protected $elements = [];

    /**
     * This array tells the indexer which properties should be indexed and what
     * weights to use for those properties.
     *
     * @var    array
     * @since  2.5
     */
    protected $instructions = [
        Indexer::TITLE_CONTEXT => ['title', 'subtitle', 'id'],
        Indexer::TEXT_CONTEXT  => ['summary', 'body'],
        Indexer::META_CONTEXT  => ['meta', 'list_price', 'sale_price'],
        Indexer::PATH_CONTEXT  => ['path', 'alias'],
        Indexer::MISC_CONTEXT  => ['comments'],
    ];

    /**
     * The indexer will use this data to create taxonomy mapping entries for
     * the item so that it can be filtered by type, label, category,
     * or whatever.
     *
     * @var    array
     * @since  2.5
     */
    protected $taxonomy = [];

    /**
     * The content URL.
     *
     * @var    string
     * @since  2.5
     */
    public $url;

    /**
     * The content route.
     *
     * @var    string
     * @since  2.5
     */
    public $route;

    /**
     * The content title.
     *
     * @var    string
     * @since  2.5
     */
    public $title;

    /**
     * The content description.
     *
     * @var    string
     * @since  2.5
     */
    public $description;

    /**
     * The published state of the result.
     *
     * @var    integer
     * @since  2.5
     */
    public $published;

    /**
     * The content published state.
     *
     * @var    integer
     * @since  2.5
     */
    public $state;

    /**
     * The content access level.
     *
     * @var    integer
     * @since  2.5
     */
    public $access;

    /**
     * The content language.
     *
     * @var    string
     * @since  2.5
     */
    public $language = '*';

    /**
     * The publishing start date.
     *
     * @var    string
     * @since  2.5
     */
    public $publish_start_date;

    /**
     * The publishing end date.
     *
     * @var    string
     * @since  2.5
     */
    public $publish_end_date;

    /**
     * The generic start date.
     *
     * @var    string
     * @since  2.5
     */
    public $start_date;

    /**
     * The generic end date.
     *
     * @var    string
     * @since  2.5
     */
    public $end_date;

    /**
     * The item list price.
     *
     * @var    mixed
     * @since  2.5
     */
    public $list_price;

    /**
     * The item sale price.
     *
     * @var    mixed
     * @since  2.5
     */
    public $sale_price;

    /**
     * The content type id. This is set by the adapter.
     *
     * @var    integer
     * @since  2.5
     */
    public $type_id;

    /**
     * The default language for content.
     *
     * @var    string
     * @since  3.0.2
     */
    public $defaultLanguage;

    /**
     * Constructor
     *
     * @since   3.0.3
     */
    public function __construct()
    {
        $this->defaultLanguage = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
    }

    /**
     * The magic set method is used to push additional values into the elements
     * array in order to preserve the cleanliness of the object.
     *
     * @param   string  $name   The name of the element.
     * @param   mixed   $value  The value of the element.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function __set($name, $value)
    {
        $this->setElement($name, $value);
    }

    /**
     * The magic get method is used to retrieve additional element values from the elements array.
     *
     * @param   string  $name  The name of the element.
     *
     * @return  mixed  The value of the element if set, null otherwise.
     *
     * @since   2.5
     */
    public function __get($name)
    {
        return $this->getElement($name);
    }

    /**
     * The magic isset method is used to check the state of additional element values in the elements array.
     *
     * @param   string  $name  The name of the element.
     *
     * @return  boolean  True if set, false otherwise.
     *
     * @since   2.5
     */
    public function __isset($name)
    {
        return isset($this->elements[$name]);
    }

    /**
     * The magic unset method is used to unset additional element values in the elements array.
     *
     * @param   string  $name  The name of the element.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function __unset($name)
    {
        unset($this->elements[$name]);
    }

    /**
     * Method to retrieve additional element values from the elements array.
     *
     * @param   string  $name  The name of the element.
     *
     * @return  mixed  The value of the element if set, null otherwise.
     *
     * @since   2.5
     */
    public function getElement($name)
    {
        // Get the element value if set.
        if (array_key_exists($name, $this->elements)) {
            return $this->elements[$name];
        }

        return null;
    }

    /**
     * Method to retrieve all elements.
     *
     * @return  array  The elements
     *
     * @since   3.8.3
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Method to set additional element values in the elements array.
     *
     * @param   string  $name   The name of the element.
     * @param   mixed   $value  The value of the element.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function setElement($name, $value)
    {
        $this->elements[$name] = $value;
    }

    /**
     * Method to get all processing instructions.
     *
     * @return  array  An array of processing instructions.
     *
     * @since   2.5
     */
    public function getInstructions()
    {
        return $this->instructions;
    }

    /**
     * Method to add a processing instruction for an item property.
     *
     * @param   string  $group     The group to associate the property with.
     * @param   string  $property  The property to process.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function addInstruction($group, $property)
    {
        // Check if the group exists. We can't add instructions for unknown groups.
        // Check if the property exists in the group.
        if (array_key_exists($group, $this->instructions) && !in_array($property, $this->instructions[$group], true)) {
            // Add the property to the group.
            $this->instructions[$group][] = $property;
        }
    }

    /**
     * Method to remove a processing instruction for an item property.
     *
     * @param   string  $group     The group to associate the property with.
     * @param   string  $property  The property to process.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function removeInstruction($group, $property)
    {
        // Check if the group exists. We can't remove instructions for unknown groups.
        if (array_key_exists($group, $this->instructions)) {
            // Search for the property in the group.
            $key = array_search($property, $this->instructions[$group]);

            // If the property was found, remove it.
            if ($key !== false) {
                unset($this->instructions[$group][$key]);
            }
        }
    }

    /**
     * Method to get the taxonomy maps for an item.
     *
     * @param   string  $branch  The taxonomy branch to get. [optional]
     *
     * @return  array  An array of taxonomy maps.
     *
     * @since   2.5
     */
    public function getTaxonomy($branch = null)
    {
        // Get the taxonomy branch if available.
        if ($branch !== null && isset($this->taxonomy[$branch])) {
            return $this->taxonomy[$branch];
        }

        return $this->taxonomy;
    }

    /**
     * Method to add a taxonomy map for an item.
     *
     * @param   string   $branch    The title of the taxonomy branch to add the node to.
     * @param   string   $title     The title of the taxonomy node.
     * @param   integer  $state     The published state of the taxonomy node. [optional]
     * @param   integer  $access    The access level of the taxonomy node. [optional]
     * @param   string   $language  The language of the taxonomy. [optional]
     *
     * @return  void
     *
     * @since   2.5
     */
    public function addTaxonomy($branch, $title, $state = 1, $access = 1, $language = '')
    {
        // We can't add taxonomies with empty titles
        if (!trim($title)) {
            return;
        }

        // Filter the input.
        $branch = preg_replace('#[^\pL\pM\pN\p{Pi}\p{Pf}\'+-.,_]+#mui', ' ', $branch);

        // Create the taxonomy node.
        $node           = new \stdClass();
        $node->title    = $title;
        $node->state    = (int) $state;
        $node->access   = (int) $access;
        $node->language = $language;
        $node->nested   = false;

        // Add the node to the taxonomy branch.
        $this->taxonomy[$branch][] = $node;
    }

    /**
     * Method to add a nested taxonomy map for an item.
     *
     * @param   string                  $branch       The title of the taxonomy branch to add the node to.
     * @param   ImmutableNodeInterface  $contentNode  The node object.
     * @param   integer                 $state        The published state of the taxonomy node. [optional]
     * @param   integer                 $access       The access level of the taxonomy node. [optional]
     * @param   string                  $language     The language of the taxonomy. [optional]
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function addNestedTaxonomy($branch, ImmutableNodeInterface $contentNode, $state = 1, $access = 1, $language = '')
    {
        // We can't add taxonomies with empty titles
        if (!trim($contentNode->title)) {
            return;
        }

        // Filter the input.
        $branch = preg_replace('#[^\pL\pM\pN\p{Pi}\p{Pf}\'+-.,_]+#mui', ' ', $branch);

        // Create the taxonomy node.
        $node           = new \stdClass();
        $node->title    = $contentNode->title;
        $node->state    = (int) $state;
        $node->access   = (int) $access;
        $node->language = $language;
        $node->nested   = true;
        $node->node     = $contentNode;

        // Add the node to the taxonomy branch.
        $this->taxonomy[$branch][] = $node;
    }

    /**
     * Method to set the item language
     *
     * @return  void
     *
     * @since   3.0
     */
    public function setLanguage()
    {
        if ($this->language == '') {
            $this->language = $this->defaultLanguage;
        }
    }

    /**
     * Helper function to serialise the data of a Result object
     *
     * @return  string  The serialised data
     *
     * @since   4.0.0
     */
    public function serialize()
    {
        return serialize($this->__serialize());
    }

    /**
     * Helper function to unserialise the data for this object
     *
     * @param   string  $serialized  Serialised data to unserialise
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function unserialize($serialized): void
    {
        $this->__unserialize(unserialize($serialized));
    }

    /**
     * Magic method used for serializing.
     *
     * @since  4.1.3
     */
    public function __serialize(): array
    {
        $taxonomy = [];

        foreach ($this->taxonomy as $branch => $nodes) {
            $taxonomy[$branch] = [];

            foreach ($nodes as $node) {
                if ($node->nested) {
                    $n = clone $node;
                    unset($n->node);
                    $taxonomy[$branch][] = $n;
                } else {
                    $taxonomy[$branch][] = $node;
                }
            }
        }

        // This order must match EXACTLY the order of the $properties in the self::__unserialize method
        return [
            $this->access,
            $this->defaultLanguage,
            $this->description,
            $this->elements,
            $this->end_date,
            $this->instructions,
            $this->language,
            $this->list_price,
            $this->publish_end_date,
            $this->publish_start_date,
            $this->published,
            $this->route,
            $this->sale_price,
            $this->start_date,
            $this->state,
            $taxonomy,
            $this->title,
            $this->type_id,
            $this->url,
        ];
    }

    /**
     * Magic method used for unserializing.
     *
     * @since  4.1.3
     */
    public function __unserialize(array $serialized): void
    {
        // This order must match EXACTLY the order of the array in the self::__serialize method
        $properties = [
            'access',
            'defaultLanguage',
            'description',
            'elements',
            'end_date',
            'instructions',
            'language',
            'list_price',
            'publish_end_date',
            'publish_start_date',
            'published',
            'route',
            'sale_price',
            'start_date',
            'state',
            'taxonomy',
            'title',
            'type_id',
            'url',
        ];

        foreach ($properties as $k => $v) {
            $this->$v = $serialized[$k];
        }

        foreach ($this->taxonomy as $nodes) {
            foreach ($nodes as $node) {
                $curTaxonomy  = Taxonomy::getTaxonomy($node->id);
                $node->state  = $curTaxonomy->state;
                $node->access = $curTaxonomy->access;
            }
        }
    }
}
