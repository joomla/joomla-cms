<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document\Renderer\Html;

use Joomla\CMS\Document\DocumentRenderer;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML document renderer for the system message queue
 *
 * @since  3.5
 */
class MessageRenderer extends DocumentRenderer
{
    /**
     * Renders the error stack and returns the results as a string
     *
     * @param   string  $name     Not used.
     * @param   array   $params   Associative array of values
     * @param   string  $content  Not used.
     *
     * @return  string  The output of the script
     *
     * @since   3.5
     */
    public function render($name, $params = [], $content = null)
    {
        $msgList     = $this->getData();
        $displayData = [
            'msgList' => $msgList,
            'name'    => $name,
            'params'  => $params,
            'content' => $content,
        ];

        $app        = Factory::getApplication();
        $chromePath = JPATH_THEMES . '/' . $app->getTemplate() . '/html/message.php';

        if (is_file($chromePath)) {
            include_once $chromePath;
        }

        if (\function_exists('renderMessage')) {
            @trigger_error(
                'renderMessage() is deprecated. Override system message rendering with layouts instead.',
                E_USER_DEPRECATED
            );

            return renderMessage($msgList);
        }

        return LayoutHelper::render('joomla.system.message', $displayData);
    }

    /**
     * Get and prepare system message data for output
     *
     * @return  array  An array contains system message
     *
     * @since   3.5
     */
    private function getData()
    {
        // Initialise variables.
        $lists = [];

        // Get the message queue
        $messages = Factory::getApplication()->getMessageQueue();

        // Build the sorted message list
        if (\is_array($messages) && !empty($messages)) {
            foreach ($messages as $msg) {
                if (isset($msg['type'], $msg['message'])) {
                    $lists[$msg['type']][] = $msg['message'];
                }
            }
        }

        return $lists;
    }
}
