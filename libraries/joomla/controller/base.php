<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Application\AbstractApplication;
use Joomla\CMS\Factory;
use Joomla\Controller\AbstractController;
use Joomla\Input\Input;

/**
 * Joomla Platform Base Controller Class
 *
 * @since       3.0.0
 * @deprecated  5.0 Use the default MVC library
 */
abstract class JControllerBase extends AbstractController implements JController
{
	/**
	 * Instantiate the controller.
	 *
	 * @param   Input                $input  The input object.
	 * @param   AbstractApplication  $app    The application object.
	 *
	 * @since  3.0.0
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
	 * @since   3.0.0
	 */
	protected function loadApplication()
	{
		$this->setApplication(Factory::getApplication());
	}

	/**
	 * Load the input object.
	 *
	 * @return  void
	 *
	 * @since   3.0.0
	 */
	protected function loadInput()
	{
		$this->setInput($this->getApplication()->input);
	}
}
