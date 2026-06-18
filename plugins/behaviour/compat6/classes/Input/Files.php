<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Input;

use Joomla\CMS\Filter\InputFilter;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Input Files Class
 *
 * @since       1.7.0
 *
 * @deprecated   4.3 will be removed in 6.0.
 *               Use Joomla\Input\Files instead
 */
class Files extends Input
{
    /**
     * The pivoted data from a $_FILES or compatible array.
     *
     * @var    array
     * @since  1.7.0
     *
     * @deprecated   4.3 will be removed in 6.0.
     *               Use Joomla\Input\Files instead
     */
    protected $decodedData = [];

    /**
     * The class constructor.
     *
     * @param   array  $source   The source argument is ignored. $_FILES is always used.
     * @param   array  $options  An optional array of configuration options:
     *                           filter : a custom InputFilter object.
     *
     * @since   3.0.0
     *
     * @deprecated   4.3 will be removed in 6.0.
     *               Use Joomla\Input\Files instead
     */
    public function __construct(?array $source = null, array $options = [])
    {
        if (isset($options['filter'])) {
            $this->filter = $options['filter'];
        } else {
            $this->filter = InputFilter::getInstance();
        }

        // Set the data source.
        $this->data = &$_FILES;

        // Set the options for the class.
        $this->options = $options;
    }

    /**
     * Gets a value from the input data.
     *
     * @param   string  $name     The name of the input property (usually the name of the files INPUT tag) to get.
     * @param   mixed   $default  The default value to return if the named property does not exist.
     * @param   string  $filter   The filter to apply to the value.
     *
     * @return  mixed  The filtered input value.
     *
     * @see     InputFilter::clean()
     * @since   1.7.0
     *
     * @deprecated   4.3 will be removed in 6.0.
     *               Use Joomla\Input\Files instead
     */
    public function get($name, $default = null, $filter = 'cmd')
    {
        if (isset($this->data[$name])) {
            $results = $this->decodeData(
                [
                    $this->data[$name]['name'],
                    $this->data[$name]['type'],
                    $this->data[$name]['tmp_name'],
                    $this->data[$name]['error'],
                    $this->data[$name]['size'],
                ]
            );

            // Prevent returning an unsafe file unless specifically requested
            if (strtoupper($filter) !== 'RAW') {
                $isSafe = InputFilter::isSafeFile($results);

                if (!$isSafe) {
                    return $default;
                }
            }

            return $results;
        }

        return $default;
    }

    /**
     * Method to decode a data array.
     *
     * @param   array  $data  The data array to decode.
     *
     * @return  array
     *
     * @since   1.7.0
     *
     * @deprecated   4.3 will be removed in 6.0.
     *               Use Joomla\Input\Files instead
     */
    protected function decodeData(array $data)
    {
        $result = [];

        if (\is_array($data[0])) {
            foreach ($data[0] as $k => $v) {
                $result[$k] = $this->decodeData([$data[0][$k], $data[1][$k], $data[2][$k], $data[3][$k], $data[4][$k]]);
            }

            return $result;
        }

        return ['name' => $data[0], 'type' => $data[1], 'tmp_name' => $data[2], 'error' => $data[3], 'size' => $data[4]];
    }

    /**
     * Sets a value.
     *
     * @param   string  $name   The name of the input property to set.
     * @param   mixed   $value  The value to assign to the input property.
     *
     * @return  void
     *
     * @since   1.7.0
     *
     * @deprecated   4.3 will be removed in 6.0.
     *               Use Joomla\Input\Files instead
     */
    public function set($name, $value)
    {
    }
}
