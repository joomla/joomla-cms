<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\Dispatcher;

/**
 * Dispatcher class for com_content
 *
 * @since  __DEPLOY_VERSION__
 */
class ContentDispatcher extends Dispatcher
{
	/**
	 * Constructor for Dispatcher
	 *
	 * @param   string               $namespace  Namespace of the Extension
	 * @param   CmsApplication       $app        The JApplication for the dispatcher
	 * @param   \JInput              $input      JInput
	 * @param   MvcFactoryInterface  $factory    The factory object for the component
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($namespace, \Joomla\CMS\Application\CmsApplication $app, \JInput $input = null, \Joomla\CMS\Mvc\Factory\MvcFactoryInterface $factory = null)
	{
		if (!$namespace)
		{
			$namespace = 'Joomla\\Component\\Content';
		}
		parent::__construct($namespace, $app, $input, $factory);
	}
}
