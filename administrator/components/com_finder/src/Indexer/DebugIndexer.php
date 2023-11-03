<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Indexer;

/**
 * Debugging indexer class for the Finder indexer package.
 *
 * @since  5.0.0
 * @internal
 */
class DebugIndexer extends Indexer
{
    /**
     * The result object from the last call to self::index()
     *
     * @var Result
     *
     * @since  5.0.0
     */
    public static $item;

    /**
     * Stub for index() in indexer class
     *
     * @param   Result  $item    Result object to index
     * @param   string  $format  Format to index
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function index($item, $format = 'html')
    {
        self::$item = $item;
    }
}
