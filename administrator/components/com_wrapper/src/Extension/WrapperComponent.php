<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_wrapper
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Wrapper\Administrator\Extension;

use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\MVCComponent;

/**
 * Component class for com_wrapper
 *
 * @since  4.0.0
 */
class WrapperComponent extends MVCComponent implements RouterServiceInterface
{
    use RouterServiceTrait;
}
