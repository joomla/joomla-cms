<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Associations\Administrator\Extension;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Extension\Component;
use Joomla\CMS\MVC\Factory\MVCFactoryServiceTrait;
use Joomla\CMS\MVC\Factory\MVCFactoryServiceInterface;

/**
 * Component class for com_associations
 *
 * @since  4.0.0
 */
class AssociationsComponent extends Component implements MVCFactoryServiceInterface
{
	use MVCFactoryServiceTrait;
}
