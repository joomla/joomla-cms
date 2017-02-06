<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTTP
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Http\TransportInterface;

/**
 * HTTP transport class interface.
 *
 * @since       11.3
 * @deprecated  5.0  Implement Joomla\Http\TransportInterface instead
 */
interface JHttpTransport extends TransportInterface
{
}
