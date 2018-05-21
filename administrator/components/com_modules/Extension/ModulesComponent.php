<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Administrator\Extension;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Extension\Component;
use Joomla\CMS\MVC\Factory\MVCFactoryServiceTrait;
use Joomla\CMS\MVC\Factory\MVCFactoryServiceInterface;

/**
 * Component class for com_modules
 *
 * @since  4.0.0
 */
class ModulesComponent extends Component implements MVCFactoryServiceInterface
{
	use MVCFactoryServiceTrait;
}
