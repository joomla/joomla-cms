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
use Joomla\Application\SessionAwareWebApplicationInterface;
use Joomla\Application\WebApplicationInterface;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Session\Session;

/**
 * Joomla HTTP driver for DebugBar
 *
 * @since   __DEPLOY_VERSION__
 */
final class JoomlaHttpDriver implements HttpDriverInterface
{
	/**
	 * @var CMSApplicationInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private $app;

	/**
	 * @var Session
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private $session;

	/**
	 * @var array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private $dummySession = [];

	/**
	 * Constructor.
	 *
	 * @param   CMSApplicationInterface  $app
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(CMSApplicationInterface $app)
	{
		$this->app = $app;

		if ($app instanceof SessionAwareWebApplicationInterface)
		{
			$this->session = $app->getSession();
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
		if ($this->app instanceof WebApplicationInterface)
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
		return $this->session ? $this->session->isStarted() : true;
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
		if ($this->session)
		{
			$this->session->set($name, $value);
		}
		else
		{
			$this->dummySession[$name] = $value;
		}
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
		return $this->session ? $this->session->has($name) : array_key_exists($name, $this->dummySession);
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
		if ($this->session)
		{
			return $this->session->get($name);
		}

		return $this->dummySession[$name] ?? null;
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
		if ($this->session)
		{
			$this->session->remove($name);
		}
		else
		{
			unset($this->dummySession[$name]);
		}
	}
}
