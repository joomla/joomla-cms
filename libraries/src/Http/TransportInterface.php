<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Http;

\defined('JPATH_PLATFORM') or die;

use Joomla\Http\TransportInterface as FrameworkTransportInterface;

/**
 * HTTP transport class interface.
 *
 * @since       1.7.3
 * @deprecated  5.0  Implement Joomla\Http\TransportInterface instead
 */
interface TransportInterface extends FrameworkTransportInterface
{
}
