<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension;

use Joomla\CMS\MVC\Factory\MVCFactoryServiceInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryServiceTrait;

/**
 * MVC Component class.
 *
 * @since  4.0.0
 */
class MVCComponent extends Component implements MVCFactoryServiceInterface
{
    use MVCFactoryServiceTrait;
}
