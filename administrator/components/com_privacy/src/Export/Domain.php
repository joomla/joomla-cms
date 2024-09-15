<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Administrator\Export;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Data object representing all data contained in a domain.
 *
 * A domain is typically a single database table and the items within the domain are separate rows from the table.
 *
 * @since  3.9.0
 */
class Domain
{
    /**
     * The name of this domain
     *
     * @var    string
     * @since  3.9.0
     */
    public $name;

    /**
     * A short description of the data in this domain
     *
     * @var    string
     * @since  3.9.0
     */
    public $description;

    /**
     * The items belonging to this domain
     *
     * @var    Item[]
     * @since  3.9.0
     */
    protected $items = [];

    /**
     * Add an item to the domain
     *
     * @param   Item  $item  The item to add
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function addItem(Item $item)
    {
        $this->items[] = $item;
    }

    /**
     * Get the domain's items
     *
     * @return  Item[]
     *
     * @since   3.9.0
     */
    public function getItems()
    {
        return $this->items;
    }
}
