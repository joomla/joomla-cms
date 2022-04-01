<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Tag;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Table\TableInterface;

/**
 * Empty Interface for a taggable Table class as a placeholder for extensions that need compatibility between Joomla
 * Note: This is an empty placeholder to ease transition to the new system in 4.0. In 4.x this interface will contain
 * 4 methods (these aren't included as they contain return typehints which would unncessarily increase the minimum PHP
 * version required to use this interface).
 *
 * @since  3.10.0
 */
interface TaggableTableInterface extends TableInterface
{
}
