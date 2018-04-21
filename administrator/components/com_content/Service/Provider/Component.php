<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Service\Provider;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Extension\ComponentInterface;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\DI\Container;

/**
 * Service provider for the com_content component.
 *
 * @since  __DEPLOY_VERSION__
 */
class Component extends \Joomla\CMS\Extension\Service\Provider\Component
{
	/**
	 * Creates the ComponentInterface instance.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @return  ComponentInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function createComponentClass(Container $container): ComponentInterface
	{
		return new ContentComponent;
	}
}
