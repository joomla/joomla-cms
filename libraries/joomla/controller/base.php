<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Application\AbstractApplication;
use Joomla\Controller\AbstractController;
use Joomla\Input\Input;

/**
 * Joomla Platform Base Controller Class
 *
 * @since  12.1
 */
abstract class JControllerBase extends AbstractController implements JController
{
	/**
	 * Instantiate the controller.
	 *
	 * @param   Input                $input  The input object.
	 * @param   AbstractApplication  $app    The application object.
	 *
	 * @since  12.1
	 */
	public function __construct(Input $input = null, AbstractApplication $app = null)
	{
		if ($app)
		{
			$this->setApplication($app);
		}
		else
		{
			$this->loadApplication();
		}

		if ($input)
		{
			$this->setInput($input);
		}
		else
		{
			$this->loadInput();
		}
	}

	/**
	 * Load the application object.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function loadApplication()
	{
		$this->setApplication(JFactory::getApplication());
	}

	/**
	 * Load the input object.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function loadInput()
	{
		$this->setInput($this->getApplication()->input);
	}
}
