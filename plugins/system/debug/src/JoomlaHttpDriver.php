<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Debug;

use DebugBar\HttpDriverInterface;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\ConsoleApplication;

/**
 * Joomla HTTP driver for DebugBar
 *
 * @since   __DEPLOY_VERSION__
 */
class JoomlaHttpDriver implements HttpDriverInterface
{
	/**
	 * @var CMSApplication|ConsoleApplication
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private $app;

	/**
	 * Constructor.
	 *
	 * @param   CMSApplication|ConsoleApplication  $app
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($app)
	{
		if ($app instanceof CMSApplication || $app instanceof ConsoleApplication)
		{
			$this->app = $app;
		}
		else
		{
			throw new \InvalidArgumentException(sprintf('Unexpected Application instance for %s', __METHOD__));
		}
	}

	/**
	 * Sets HTTP headers
	 *
	 * @param   array  $headers
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setHeaders(array $headers)
	{
		if ($this->app instanceof CMSApplication)
		{
			foreach ($headers as $name => $value)
			{
				$this->app->setHeader($name, $value, true);
			}
		}
	}

	/**
	 * Checks if the session is started
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isSessionStarted()
	{
		return $this->app->getSession()->isStarted();
	}

	/**
	 * Sets a value in the session
	 *
	 * @param   string  $name
	 * @param   string  $value
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setSessionValue($name, $value)
	{
		$this->app->getSession()->set($name, $value);
	}

	/**
	 * Checks if a value is in the session
	 *
	 * @param   string  $name
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function hasSessionValue($name)
	{
		return $this->app->getSession()->has($name);
	}

	/**
	 * Returns a value from the session
	 *
	 * @param   string  $name
	 *
	 * @return  mixed
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getSessionValue($name)
	{
		return $this->app->getSession()->get($name);
	}

	/**
	 * Deletes a value from the session
	 *
	 * @param string $name
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function deleteSessionValue($name)
	{
		$this->app->getSession()->remove($name);
	}
}
