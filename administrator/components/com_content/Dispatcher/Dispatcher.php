<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Dispatcher;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\HTML\Registry;
use Joomla\Component\Content\Administrator\Service\HTML\AdministratorService;

/**
 * Dispatcher class for com_content
 *
 * @since  4.0.0
 */
class Dispatcher extends \Joomla\CMS\Dispatcher\Dispatcher
{
	/**
	 * The extension namespace
	 *
	 * @var    string
	 *
	 * @since  4.0.0
	 */
	protected $namespace = 'Joomla\\Component\\Content';

	/**
	 * Subclasses can register here HTML services.
	 *
	 * @param   Registry  $registry  The registry
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function loadHTMLServices(Registry $registry)
	{
		$registry->register('contentadministrator', new AdministratorService);
	}
}
