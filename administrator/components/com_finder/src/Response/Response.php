<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Response;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Finder Indexer JSON Response Class
 *
 * @since  2.5
 */
class Response
{
    /**
     * The buffer
     *
     * @var    string
     * @since  4.3.0
     */
    public $buffer;

    /**
     * The memory
     *
     * @var    string
     * @since  4.3.0
     */
    public $memory;

    /**
     * If it has an error
     *
     * @var    bool
     * @since  4.3.0
     */
    public $error;

    /**
     * The header
     *
     * @var    string
     * @since  4.3.0
     */
    public $header;

    /**
     * The message
     *
     * @var    string
     * @since  4.3.0
     */
    public $message;

    /**
     * The batch size
     *
     * @var    int
     * @since  4.3.0
     */
    public $batchSize;

    /**
     * The batch offset
     *
     * @var    int
     * @since  4.3.0
     */
    public $batchOffset;

    /**
     * The total items
     *
     * @var    int
     * @since  4.3.0
     */
    public $totalItems;

    /**
     * The plugin state
     *
     * @var    string
     * @since  4.3.0
     */
    public $pluginState;

    /**
     * The start time
     *
     * @var    string
     * @since  4.3.0
     */
    public $startTime;

    /**
     * The end time
     *
     * @var    string
     * @since  4.3.0
     */
    public $endTime;

    /**
     * The start
     *
     * @var    int
     * @since  4.3.0
     */
    public $start;

    /**
     * The complete
     *
     * @var    int
     * @since  4.3.0
     */
    public $complete;

    /**
     * Class Constructor
     *
     * @param   mixed  $state  The processing state for the indexer
     *
     * @since   2.5
     */
    public function __construct($state)
    {
        $params = ComponentHelper::getParams('com_finder');

        if ($params->get('enable_logging', '0')) {
            $options['format']    = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
            $options['text_file'] = 'indexer.php';
            Log::addLogger($options);
        }

        // Check if we are dealing with an error.
        if ($state instanceof \Exception) {
            // Log the error
            try {
                Log::add($state->getMessage(), Log::ERROR);
            } catch (\RuntimeException $exception) {
                // Informational log only
            }

            // Prepare the error response.
            $this->error   = true;
            $this->header  = Text::_('COM_FINDER_INDEXER_HEADER_ERROR');
            $this->message = $state->getMessage();
        } else {
            // Prepare the response data.
            $this->batchSize   = (int) $state->batchSize;
            $this->batchOffset = (int) $state->batchOffset;
            $this->totalItems  = (int) $state->totalItems;
            $this->pluginState = $state->pluginState;

            $this->startTime = $state->startTime;
            $this->endTime   = Factory::getDate()->toSql();

            $this->start    = !empty($state->start) ? (int) $state->start : 0;
            $this->complete = !empty($state->complete) ? (int) $state->complete : 0;

            // Set the appropriate messages.
            if ($this->totalItems <= 0 && $this->complete) {
                $this->header  = Text::_('COM_FINDER_INDEXER_HEADER_COMPLETE');
                $this->message = Text::_('COM_FINDER_INDEXER_MESSAGE_COMPLETE');
            } elseif ($this->totalItems <= 0) {
                $this->header  = Text::_('COM_FINDER_INDEXER_HEADER_OPTIMIZE');
                $this->message = Text::_('COM_FINDER_INDEXER_MESSAGE_OPTIMIZE');
            } else {
                $this->header  = Text::_('COM_FINDER_INDEXER_HEADER_RUNNING');
                $this->message = Text::_('COM_FINDER_INDEXER_MESSAGE_RUNNING');
            }
        }
    }
}
