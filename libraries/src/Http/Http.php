<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Http;

use Joomla\Http\Http as FrameworkHttp;
use Joomla\Http\TransportInterface as FrameworkTransportInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTTP client class.
 *
 * @since  1.7.3
 */
class Http extends FrameworkHttp
{
    /**
     * Constructor.
     *
     * @param   array|\ArrayAccess           $options    Client options array. If the registry contains any headers.* elements,
     *                                                   these will be added to the request headers.
     * @param   FrameworkTransportInterface  $transport  The HTTP transport object.
     *
     * @since   1.7.3
     * @throws  \InvalidArgumentException
     */
    public function __construct($options = [], FrameworkTransportInterface $transport = null)
    {
        if (!\is_array($options) && !($options instanceof \ArrayAccess)) {
            throw new \InvalidArgumentException(
                'The options param must be an array or implement the ArrayAccess interface.'
            );
        }

        $this->options = $options;

        if (!isset($transport)) {
            $transport = HttpFactory::getAvailableDriver($this->options);
        }

        // Ensure the transport is a framework TransportInterface instance or bail out
        if (!($transport instanceof FrameworkTransportInterface)) {
            throw new \InvalidArgumentException('A valid TransportInterface object was not set.');
        }

        $this->transport = $transport;
    }
}
