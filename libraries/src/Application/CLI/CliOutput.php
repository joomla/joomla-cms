<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Application\CLI;

use Joomla\CMS\Application\CLI\Output\Processor\ProcessorInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base class defining a command line output handler
 *
 * @since       4.0.0
 *
 * @deprecated  4.3 will be removed in 6.0
 *              Use the `joomla/console` package instead
 */
abstract class CliOutput
{
    /**
     * Output processing object
     *
     * @var    ProcessorInterface
     * @since  4.0.0
     */
    protected $processor;

    /**
     * Constructor
     *
     * @param   ProcessorInterface  $processor  The output processor.
     *
     * @since   4.0.0
     */
    public function __construct(ProcessorInterface $processor = null)
    {
        $this->setProcessor($processor ?: new Output\Processor\ColorProcessor());
    }

    /**
     * Set a processor
     *
     * @param   ProcessorInterface  $processor  The output processor.
     *
     * @return  $this
     *
     * @since   4.0.0
     */
    public function setProcessor(ProcessorInterface $processor)
    {
        $this->processor = $processor;

        return $this;
    }

    /**
     * Get a processor
     *
     * @return  ProcessorInterface
     *
     * @since   4.0.0
     * @throws  \RuntimeException
     */
    public function getProcessor()
    {
        if ($this->processor) {
            return $this->processor;
        }

        throw new \RuntimeException('A ProcessorInterface object has not been set.');
    }

    /**
     * Write a string to an output handler.
     *
     * @param   string   $text  The text to display.
     * @param   boolean  $nl    True (default) to append a new line at the end of the output string.
     *
     * @return  $this
     *
     * @since   4.0.0
     * @codeCoverageIgnore
     */
    abstract public function out($text = '', $nl = true);
}
