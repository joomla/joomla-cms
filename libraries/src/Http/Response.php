<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Http;

use Joomla\Http\Response as FrameworkResponse;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTTP response data object class.
 *
 * @since       1.7.3
 *
 * @deprecated  4.0 will be removed in 7.0
 *              Use Joomla\Http\Response instead
 */
class Response extends FrameworkResponse
{
    /**
     * Magic getter for backward compatibility with the 1.x Joomla Framework API
     *
     * @param   string  $name  The variable to return
     *
     * @return  mixed
     *
     * @since   6.0.0
     * @deprecated  6.0.0 will be removed in 7.0
     *              Access data via the PSR-7 ResponseInterface instead
     */
    public function __get($name)
    {
        switch (strtolower($name)) {
            case 'body':
                $stream = $this->getBody();
                $stream->rewind();
                return $stream->getContents();

            case 'code':
                return $this->getStatusCode();

            case 'headers':
                return $this->getHeaders();

            default:
                $trace = debug_backtrace();

                trigger_error(
                    \sprintf(
                        'Undefined property via __get(): %s in %s on line %s',
                        $name,
                        $trace[0]['file'],
                        $trace[0]['line']
                    ),
                    E_USER_NOTICE
                );

                break;
        }
    }
}
