<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Input;

use Joomla\CMS\Filter\InputFilter;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Input Base Class
 *
 * This is an abstracted input class used to manage retrieving data from the application environment.
 *
 * @since       1.7.0
 *
 * @deprecated   4.3 will be removed in 6.0.
 *               Use Joomla\Input\Input instead
 *
 * @property-read   Input   $get
 * @property-read   Input   $post
 * @property-read   Input   $request
 * @property-read   Input   $server
 * @property-read   Input   $env
 * @property-read   Files   $files
 * @property-read   Cookie  $cookie
 * @property-read   Json    $json
 */
class Input extends \Joomla\Input\Input
{
    /**
     * Container with allowed superglobals
     *
     * @var    array
     * @since  3.8.9
     *
     * @deprecated   4.3 will be removed in 6.0.
     *               Use Joomla\Input\Input instead
     */
    private static $allowedGlobals = ['REQUEST', 'GET', 'POST', 'FILES', 'SERVER', 'ENV'];

    /**
     * Input objects
     *
     * @var    Input[]
     * @since  1.7.0
     *
     * @deprecated   4.3 will be removed in 6.0.
     *               Use Joomla\Input\Input instead
     */
    protected $inputs = [];

    /**
     * Constructor.
     *
     * @param   array  $source   Source data (Optional, default is $_REQUEST)
     * @param   array  $options  Array of configuration parameters (Optional)
     *
     * @since   1.7.0
     *
     * @deprecated   4.3 will be removed in 6.0.
     *               Use Joomla\Input\Input instead
     */
    public function __construct($source = null, array $options = [])
    {
        if (!isset($options['filter'])) {
            $this->filter = InputFilter::getInstance();
        }

        parent::__construct($source, $options);
    }

    /**
     * Magic method to get an input object
     *
     * @param   mixed  $name  Name of the input object to retrieve.
     *
     * @return  \Joomla\Input\Input  The request input object
     *
     * @since   1.7.0
     *
     * @deprecated   4.3 will be removed in 6.0.
     *               Use Joomla\Input\Input instead
     */
    public function __get($name)
    {
        if (isset($this->inputs[$name])) {
            return $this->inputs[$name];
        }

        $className = '\\Joomla\\CMS\\Input\\' . ucfirst($name);

        if (class_exists($className)) {
            $this->inputs[$name] = new $className(null, $this->options);

            return $this->inputs[$name];
        }

        $superGlobal = '_' . strtoupper($name);

        if (\in_array(strtoupper($name), self::$allowedGlobals, true) && isset($GLOBALS[$superGlobal])) {
            $this->inputs[$name] = new Input($GLOBALS[$superGlobal], $this->options);

            return $this->inputs[$name];
        }

        // Try using the parent class
        return parent::__get($name);
    }

    /**
     * Gets an array of values from the request.
     *
     * @param   array   $vars           Associative array of keys and filter types to apply.
     *                                  If empty and datasource is null, all the input data will be returned
     *                                  but filtered using the filter given by the parameter defaultFilter in
     *                                  InputFilter::clean.
     * @param   mixed   $datasource     Array to retrieve data from, or null.
     * @param   string  $defaultFilter  Default filter used in InputFilter::clean if vars is empty and
     *                                  datasource is null. If 'unknown', the default case is used in
     *                                  InputFilter::clean.
     *
     * @return  mixed  The filtered input data.
     *
     * @since   1.7.0
     *
     * @deprecated   4.3 will be removed in 6.0.
     *               Use Joomla\Input\Input instead
     */
    public function getArray(array $vars = [], $datasource = null, $defaultFilter = 'unknown')
    {
        return $this->getArrayRecursive($vars, $datasource, $defaultFilter, false);
    }

    /**
     * Gets an array of values from the request.
     *
     * @param   array   $vars           Associative array of keys and filter types to apply.
     *                                  If empty and datasource is null, all the input data will be returned
     *                                  but filtered using the filter given by the parameter defaultFilter in
     *                                  InputFilter::clean.
     * @param   mixed   $datasource     Array to retrieve data from, or null.
     * @param   string  $defaultFilter  Default filter used in InputFilter::clean if vars is empty and
     *                                  datasource is null. If 'unknown', the default case is used in
     *                                  InputFilter::clean.
     * @param   bool    $recursion      Flag to indicate a recursive function call.
     *
     * @return  mixed  The filtered input data.
     *
     * @since   3.4.2
     *
     * @deprecated   4.3 will be removed in 6.0.
     *               Use Joomla\Input\Input instead
     */
    protected function getArrayRecursive(array $vars = [], $datasource = null, $defaultFilter = 'unknown', $recursion = false)
    {
        if (empty($vars) && \is_null($datasource)) {
            $vars = $this->data;
        } else {
            if (!$recursion) {
                $defaultFilter = null;
            }
        }

        $results = [];

        foreach ($vars as $k => $v) {
            if (\is_array($v)) {
                if (\is_null($datasource)) {
                    $results[$k] = $this->getArrayRecursive($v, $this->get($k, null, 'array'), $defaultFilter, true);
                } else {
                    $results[$k] = $this->getArrayRecursive($v, $datasource[$k], $defaultFilter, true);
                }
            } else {
                $filter = $defaultFilter ?? $v;

                if (\is_null($datasource)) {
                    $results[$k] = $this->get($k, null, $filter);
                } elseif (isset($datasource[$k])) {
                    $results[$k] = $this->filter->clean($datasource[$k], $filter);
                } else {
                    $results[$k] = $this->filter->clean(null, $filter);
                }
            }
        }

        return $results;
    }

    /**
     * Method to unserialize the input.
     *
     * @param   string  $input  The serialized input.
     *
     * @return  void
     *
     * @since   3.0.0
     *
     * @deprecated   4.3 will be removed in 6.0.
     *               Use Joomla\Input\Input instead
     */
    public function unserialize($input)
    {
        // Unserialize the options, data, and inputs.
        list($this->options, $this->data, $this->inputs) = unserialize($input);

        // Load the filter.
        if (isset($this->options['filter'])) {
            $this->filter = $this->options['filter'];
        } else {
            $this->filter = InputFilter::getInstance();
        }
    }
}
