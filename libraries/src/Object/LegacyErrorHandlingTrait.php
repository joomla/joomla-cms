<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Object;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Trait which contains the legacy methods that formerly were inherited from \Joomla\CMS\Object\CMSObject to set and
 * get errors from the model. Strategically deprecated in favour of exceptions which implement the interface
 * \Joomla\CMS\MVC\Model\Exception\ModelExceptionInterface
 *
 * @since       __DEPLOY_VERSION__
 * @deprecated  6.0 Will be removed in favour of throwing exceptions
 */
trait LegacyErrorHandlingTrait
{
    /**
     * An array of error messages or Exception objects.
     *
     * @var    array
     * @since  1.7.0
     * @deprecated  3.1.4  JError has been deprecated
     */
    // phpcs:disable PSR2.Classes.PropertyDeclaration
    protected $_errors = array();
    // phpcs:enable PSR2.Classes.PropertyDeclaration

    /**
     * Get the most recent error message.
     *
     * @param   integer  $i         Option error index.
     * @param   boolean  $toString  Indicates if Exception objects should return their error message.
     *
     * @return  string   Error message
     *
     * @since   1.7.0
     * @deprecated 3.1.4  JError has been deprecated
     */
    public function getError($i = null, $toString = true)
    {
        // Find the error
        if ($i === null) {
            // Default, return the last message
            $error = end($this->_errors);
        } elseif (!\array_key_exists($i, $this->_errors)) {
            // If $i has been specified but does not exist, return false
            return false;
        } else {
            $error = $this->_errors[$i];
        }

        // Check if only the string is requested
        if ($error instanceof \Exception && $toString) {
            return $error->getMessage();
        }

        return $error;
    }

    /**
     * Return all errors, if any.
     *
     * @return  array  Array of error messages.
     *
     * @since   1.7.0
     * @deprecated 3.1.4  JError has been deprecated
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Add an error message.
     *
     * @param   string  $error  Error message.
     *
     * @return  void
     *
     * @since   1.7.0
     * @deprecated 3.1.4  JError has been deprecated
     */
    public function setError($error)
    {
        $this->_errors[] = $error;
    }
}
