<?php

/**
 * @package     Joomla.Installation
 * @subpackage  Error
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Error\Renderer;

use Joomla\CMS\Error\AbstractRenderer;
use Joomla\CMS\Installation\Response\JsonResponse;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * JSON error page renderer for the installation application
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
        return json_encode(new JsonResponse($error));
    }
}
