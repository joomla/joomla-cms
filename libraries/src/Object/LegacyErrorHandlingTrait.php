<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Object;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Trait which contains the legacy methods that formerly were inherited from \Joomla\CMS\Object\CMSObject to set and
 * get errors in a class.
 *
 * @since       4.3.0
 *
 * @deprecated  4.3 will be removed in 7.0
 *              Will be removed without replacement
 *              Throw an Exception instead of setError
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
    protected $_errors = [];
    // phpcs:enable PSR2.Classes.PropertyDeclaration

    /**
     * Use exceptions rather than getError/setError.
     *
     * @var          boolean
     * @since        __DEPLOY_VERSION__
     * @deprecated   7.0
     */
    private bool $useExceptions = false;

    /**
     * Get the most recent error message.
     *
     * @param   integer  $i         Option error index.
     * @param   boolean  $toString  Indicates if Exception objects should return their error message.
     *
     * @return  string   Error message
     *
     * @since   1.7.0
     *
     * @deprecated  3.1.4 will be removed in 7.0
     *              Will be removed without replacement
     *              Catch thrown Exceptions instead of getError
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
     *
     * @deprecated  3.1.4 will be removed in 7.0
     *              Will be removed without replacement
     *              Catch thrown Exceptions instead of getErrors
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
     *
     * @deprecated  3.1.4 will be removed in 7.0
     *              Will be removed without replacement
     *              Throw an Exception instead of using setError
     */
    public function setError($error)
    {
        if (!$this->useExceptions || $error === '') {
            $this->_errors[] = $error;
        } else {
            throw new \Exception($error, 500);
        }
    }

    /**
     * If true then subclasses should throw exceptions rather than use getError and setError.
     *
     * @return  boolean
     *
     * @since        __DEPLOY_VERSION__
     * @deprecated   7.0
     */
    public function shouldUseExceptions(): bool
    {
        return $this->useExceptions;
    }

    /**
     * If true then subclasses should throw exceptions rather than use getError and setError.
     *
     * @param   boolean   $value  The value to set for the field.
     *
     * @return  void
     *
     * @since        __DEPLOY_VERSION__
     * @deprecated   7.0
     */
    public function setUseExceptions(bool $value): void
    {
        $this->useExceptions = $value;
    }
}
