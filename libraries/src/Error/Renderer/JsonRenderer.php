<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Error\Renderer;

use Joomla\Application\WebApplicationInterface;
use Joomla\CMS\Error\AbstractRenderer;
use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * JSON error page renderer
 *
 * @since  4.0.0
 */
class JsonRenderer extends AbstractRenderer
{
    /**
     * The format (type) of the error page
     *
     * @var    string
     * @since  4.0.0
     */
    protected $type = 'json';

    /**
     * Render the error page for the given object
     *
     * @param   \Throwable  $error  The error object to be rendered
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public function render(\Throwable $error): string
    {
        // Create our data object to be rendered
        $data = [
            'error'   => true,
            'code'    => $error->getCode(),
            'message' => $error->getMessage(),
        ];

        // Include the stack trace if in debug mode
        if (JDEBUG) {
            $data['trace'] = $error->getTraceAsString();
        }

        $app = Factory::getApplication();

        if ($app instanceof WebApplicationInterface) {
            $errorCode = 500;

            if ($error->getCode() > 0) {
                $errorCode = $error->getCode();
            }

            $app->setHeader('status', $errorCode);
        }

        // Push the data object into the document
        $this->getDocument()->setBuffer(json_encode($data));

        if (ob_get_contents()) {
            ob_end_clean();
        }

        return $this->getDocument()->render();
    }
}
