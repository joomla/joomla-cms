<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Object;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla Platform Object Class
 *
 * This class allows for simple but smart objects with get and set methods
 * and an internal error handler.
 *
 * @since       1.7.0
 *
 * @deprecated  4.3 will be removed in 6.0
 *              Use \stdClass or \Joomla\Registry\Registry instead.
 *              Example: new \Joomla\Registry\Registry();
 */
class CMSObject extends \stdClass
{
    use LegacyErrorHandlingTrait;
    use LegacyPropertyManagementTrait;

    /**
     * Class constructor, overridden in descendant classes.
     *
     * @param   mixed  $properties  Either and associative array or another
     *                              object to set the initial properties of the object.
     *
     * @since   1.7.0
     */
    public function __construct($properties = null)
    {
        if ($properties !== null) {
            $this->setProperties($properties);
        }
    }

    /**
     * Magic method to convert the object to a string gracefully.
     *
     * @return  string  The classname.
     *
     * @since   1.7.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Classes should provide their own __toString() implementation.
     */
    public function __toString()
    {
        return \get_class($this);
    }
}
