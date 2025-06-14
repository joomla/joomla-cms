<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Response;

use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * JSON Response class.
 *
 * This class serves to provide the Joomla Platform with a common interface to access
 * response variables for e.g. Ajax requests.
 *
 * @since  3.1
 */
class JsonResponse
{
    /**
     * Determines whether the request was successful
     *
     * @var    boolean
     *
     * @since  3.1
     */
    public $success = true;

    /**
     * The main response message
     *
     * @var    string
     *
     * @since  3.1
     */
    public $message = null;

    /**
     * Array of messages gathered in the Application object
     *
     * @var    array
     *
     * @since  3.1
     */
    public $messages = null;

    /**
     * The response data
     *
     * @var    mixed
     *
     * @since  3.1
     */
    public $data = null;

    /**
     * Constructor
     *
     * @param   mixed    $response        The Response data
     * @param   string   $message         The main response message
     * @param   boolean  $error           True, if the success flag shall be set to false, defaults to false
     * @param   boolean  $ignoreMessages  True, if the message queue shouldn't be included, defaults to false
     *
     * @since   3.1
     */
    public function __construct($response = null, $message = null, $error = false, $ignoreMessages = false)
    {
        $this->message = $message;

        // Get the message queue if requested and available
        $app = Factory::getApplication();

        if (!$ignoreMessages && $app !== null && \is_callable([$app, 'getMessageQueue'])) {
            $messages = $app->getMessageQueue();
            $lists    = [];

            // Build the sorted messages list
            if (\is_array($messages) && \count($messages)) {
                foreach ($messages as $message) {
                    if (isset($message['type'], $message['message'])) {
                        $lists[$message['type']][] = $message['message'];
                    }
                }
            }

            // If messages exist add them to the output
            if (\count($lists)) {
                $this->messages = $lists;
            }
        }

        // Check if we are dealing with an error
        if ($response instanceof \Throwable) {
            // Prepare the error response
            $this->success = false;
            $this->message = $response->getMessage();
        } else {
            // Prepare the response data
            $this->success = !$error;
            $this->data    = $response;
        }
    }

    /**
     * Magic toString method for sending the response in JSON format
     *
     * @return  string  The response in JSON format
     *
     * @since   3.1
     */
    public function __toString()
    {
        return json_encode($this);
    }
}
