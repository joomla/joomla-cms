<?php

/**
 * @package     Joomla.Installation
 * @subpackage  Response
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Response;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * JSON Response class for the Joomla Installer.
 *
 * @since  3.1
 */
class JsonResponse
{
    /**
     * The security token.
     *
     * @var    string
     * @since  4.3.0
     */
    public $token;

    /**
     * The language tag
     *
     * @var    string
     * @since  4.3.0
     */
    public $lang;

    /**
     * The message
     *
     * @var    string
     * @since  4.3.0
     */
    public $message;

    /**
     * The messages array
     *
     * @var    array
     * @since  4.3.0
     */
    public $messages;

    /**
     * The error message
     *
     * @var    string
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
     * The data
     *
     * @var    mixed
     * @since  4.3.0
     */
    public $data;

    /**
     * Constructor for the JSON response
     *
     * @param   mixed  $data  Exception if there is an error, otherwise, the session data
     *
     * @since   3.1
     */
    public function __construct($data)
    {
        // The old token is invalid so send a new one.
        $this->token = Session::getFormToken(true);

        // Get the language and send its tag along
        $this->lang = Factory::getLanguage()->getTag();

        // Get the message queue
        $messages = Factory::getApplication()->getMessageQueue();

        // Build the sorted message list
        if (\is_array($messages) && \count($messages)) {
            foreach ($messages as $msg) {
                if (isset($msg['type'], $msg['message'])) {
                    $lists[$msg['type']][] = $msg['message'];
                }
            }
        }

        // If messages exist add them to the output
        if (isset($lists) && \is_array($lists)) {
            $this->messages = $lists;
        }

        // Check if we are dealing with an error.
        if ($data instanceof \Throwable) {
            // Prepare the error response.
            $this->error   = true;
            $this->header  = Text::_('INSTL_HEADER_ERROR');
            $this->message = $data->getMessage();
        } else {
            // Prepare the response data.
            $this->error = false;

            if (isset($data->error) && $data->error) {
                $this->error = true;
            }

            $this->data  = $data;
        }
    }
}
