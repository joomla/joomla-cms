<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Input;

use Joomla\CMS\Filter\InputFilter;

/**
 * Joomla! Input CLI Class
 *
 * @since       1.7.0
 * @deprecated  5.0  Use the `joomla/console` package instead
 */
class Cli extends Input
{
    /**
     * The executable that was called to run the CLI script.
     *
     * @var    string
     * @since  1.7.0
     * @deprecated  5.0  Use the `joomla/console` package instead
     */
    public $executable;

    /**
     * The additional arguments passed to the script that are not associated
     * with a specific argument name.
     *
     * @var    array
     * @since  1.7.0
     * @deprecated  5.0  Use the `joomla/console` package instead
     */
    public $args = [];

    /**
     * Constructor.
     *
     * @param   array  $source   Source data (Optional, default is $_REQUEST)
     * @param   array  $options  Array of configuration parameters (Optional)
     *
     * @since   1.7.0
     * @deprecated  5.0  Use the `joomla/console` package instead
     */
    public function __construct(array $source = null, array $options = [])
    {
        if (isset($options['filter'])) {
            $this->filter = $options['filter'];
        } else {
            $this->filter = InputFilter::getInstance();
        }

        // Get the command line options
        $this->parseArguments();

        // Set the options for the class.
        $this->options = $options;
    }

    /**
     * Method to serialize the input.
     *
     * @return  string  The serialized input.
     *
     * @since   3.0.0
     * @deprecated  5.0  Use the `joomla/console` package instead
     */
    public function serialize()
    {
        // Load all of the inputs.
        $this->loadAllInputs();

        // Remove $_ENV and $_SERVER from the inputs.
        $inputs = $this->inputs;
        unset($inputs['env']);
        unset($inputs['server']);

        // Serialize the executable, args, options, data, and inputs.
        return serialize([$this->executable, $this->args, $this->options, $this->data, $inputs]);
    }

    /**
     * Method to unserialize the input.
     *
     * @param   string  $input  The serialized input.
     *
     * @return  Input  The input object.
     *
     * @since   3.0.0
     * @deprecated  5.0  Use the `joomla/console` package instead
     */
    public function unserialize($input)
    {
        // Unserialize the executable, args, options, data, and inputs.
        [$this->executable, $this->args, $this->options, $this->data, $this->inputs] = unserialize($input);

        // Load the filter.
        if (isset($this->options['filter'])) {
            $this->filter = $this->options['filter'];
        } else {
            $this->filter = InputFilter::getInstance();
        }
    }

    /**
     * Initialise the options and arguments
     *
     * Not supported: -abc c-value
     *
     * @return  void
     *
     * @since   1.7.0
     * @deprecated  5.0  Use the `joomla/console` package instead
     */
    protected function parseArguments()
    {
        $argv = $_SERVER['argv'];

        $this->executable = array_shift($argv);

        $out = [];

        for ($i = 0, $j = is_countable($argv) ? \count($argv) : 0; $i < $j; $i++) {
            $arg = $argv[$i];

            // --foo --bar=baz
            if (str_starts_with((string) $arg, '--')) {
                $eqPos = strpos((string) $arg, '=');

                // --foo
                if ($eqPos === false) {
                    $key = substr((string) $arg, 2);

                    // --foo value
                    if ($i + 1 < $j && $argv[$i + 1][0] !== '-') {
                        $value = $argv[$i + 1];
                        $i++;
                    } else {
                        $value = $out[$key] ?? true;
                    }

                    $out[$key] = $value;
                } else {
                    // --bar=baz
                    $key = substr((string) $arg, 2, $eqPos - 2);
                    $value = substr((string) $arg, $eqPos + 1);
                    $out[$key] = $value;
                }
            } elseif (str_starts_with((string) $arg, '-')) {
            // -k=value -abc
            // -k=value
                if (substr((string) $arg, 2, 1) === '=') {
                    $key = substr((string) $arg, 1, 1);
                    $value = substr((string) $arg, 3);
                    $out[$key] = $value;
                } else // -abc
                {
                    $chars = str_split(substr((string) $arg, 1));

                    foreach ($chars as $char) {
                        $key = $char;
                        $value = $out[$key] ?? true;
                        $out[$key] = $value;
                    }

                    // -a a-value
                    if ((\count($chars) === 1) && ($i + 1 < $j) && ($argv[$i + 1][0] !== '-')) {
                        $out[$key] = $argv[$i + 1];
                        $i++;
                    }
                }
            } else {
                // Plain-arg
                $this->args[] = $arg;
            }
        }

        $this->data = $out;
    }
}
