<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Pathway;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class to maintain a pathway.
 *
 * The user's navigated path within the application.
 *
 * @since  1.5
 */
class Pathway
{
    /**
     * Array to hold the pathway item objects
     *
     * @var    array
     * @since  4.0.0
     */
    protected $pathway = [];

    /**
     * Integer number of items in the pathway
     *
     * @var    integer
     * @since  4.0.0
     */
    protected $count = 0;

    /**
     * Pathway instances container.
     *
     * @var    Pathway[]
     * @since  1.7
     */
    protected static $instances = [];

    /**
     * Returns a Pathway object
     *
     * @param   string  $client  The name of the client
     *
     * @return  Pathway  A Pathway object.
     *
     * @since       1.5
     * @throws      \RuntimeException
     * @deprecated  5.0 Get the instance from the application, eg. $application->getPathway()
     */
    public static function getInstance($client)
    {
        if (empty(self::$instances[$client])) {
            // Create a Pathway object
            $name = ucfirst($client) . 'Pathway';

            if (!Factory::getContainer()->has($name)) {
                throw new \RuntimeException(Text::sprintf('JLIB_APPLICATION_ERROR_PATHWAY_LOAD', $client), 500);
            }

            self::$instances[$client] = Factory::getContainer()->get($name);
        }

        return self::$instances[$client];
    }

    /**
     * Return the Pathway items array
     *
     * @return  array  Array of pathway items
     *
     * @since   1.5
     */
    public function getPathway()
    {
        // Use array_values to reset the array keys numerically
        return array_values($this->pathway);
    }

    /**
     * Set the Pathway items array.
     *
     * @param   array  $pathway  An array of pathway objects.
     *
     * @return  array  The previous pathway data.
     *
     * @since   1.5
     */
    public function setPathway($pathway)
    {
        $oldPathway = $this->pathway;

        // Set the new pathway.
        $this->pathway = array_values((array) $pathway);

        return array_values($oldPathway);
    }

    /**
     * Create and return an array of the pathway names.
     *
     * @return  array  Array of names of pathway items
     *
     * @since   1.5
     */
    public function getPathwayNames()
    {
        $names = [];

        // Build the names array using just the names of each pathway item
        foreach ($this->pathway as $item) {
            $names[] = $item->name;
        }

        // Use array_values to reset the array keys numerically
        return array_values($names);
    }

    /**
     * Create and add an item to the pathway.
     *
     * @param   string  $name  The name of the item.
     * @param   string  $link  The link to the item.
     *
     * @return  boolean  True on success
     *
     * @since   1.5
     */
    public function addItem($name, $link = '')
    {
        $ret = false;

        if ($this->pathway[] = $this->makeItem($name, $link)) {
            $ret = true;
            $this->count++;
        }

        return $ret;
    }

    /**
     * Set item name.
     *
     * @param   integer  $id    The id of the item on which to set the name.
     * @param   string   $name  The name to set.
     *
     * @return  boolean  True on success
     *
     * @since   1.5
     */
    public function setItemName($id, $name)
    {
        $ret = false;

        if (isset($this->pathway[$id])) {
            $this->pathway[$id]->name = $name;
            $ret = true;
        }

        return $ret;
    }

    /**
     * Create and return a new pathway object.
     *
     * @param   string  $name  Name of the item
     * @param   string  $link  Link to the item
     *
     * @return  \stdClass  Pathway item object
     *
     * @since   3.1
     */
    protected function makeItem($name, $link)
    {
        $item = new \stdClass();
        $item->name = html_entity_decode($name, ENT_COMPAT, 'UTF-8');
        $item->link = $link;

        return $item;
    }
}
