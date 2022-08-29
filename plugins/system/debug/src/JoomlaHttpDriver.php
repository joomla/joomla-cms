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

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla HTTP driver for DebugBar
 *
 * @since   4.1.5
 */
final class JoomlaHttpDriver implements HttpDriverInterface
{
    /**
     * @var CMSApplicationInterface
     *
     * @since   4.1.5
     */
    private $app;

    /**
     * @var Session
     *
     * @since   4.1.5
     */
    private $session;

    /**
     * @var array
     *
     * @since   4.1.5
     */
    private $dummySession = [];

    /**
     * Constructor.
     *
     * @param   CMSApplicationInterface  $app
     *
     * @since   4.1.5
     */
    public function __construct(CMSApplicationInterface $app)
    {
        $this->app = $app;

        if ($app instanceof SessionAwareWebApplicationInterface) {
            $this->session = $app->getSession();
        }
    }

    /**
     * Sets HTTP headers
     *
     * @param   array  $headers
     *
     * @since   4.1.5
     */
    public function setHeaders(array $headers)
    {
        if ($this->app instanceof WebApplicationInterface) {
            foreach ($headers as $name => $value) {
                $this->app->setHeader($name, $value, true);
            }
        }
    }

    /**
     * Checks if the session is started
     *
     * @return  boolean
     *
     * @since   4.1.5
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
     * @since   4.1.5
     */
    public function setSessionValue($name, $value)
    {
        if ($this->session) {
            $this->session->set($name, $value);
        } else {
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
     * @since   4.1.5
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
     * @since   4.1.5
     */
    public function getSessionValue($name)
    {
        if ($this->session) {
            return $this->session->get($name);
        }

        return $this->dummySession[$name] ?? null;
    }

    /**
     * Deletes a value from the session
     *
     * @param string $name
     *
     * @since   4.1.5
     */
    public function deleteSessionValue($name)
    {
        if ($this->session) {
            $this->session->remove($name);
        } else {
            unset($this->dummySession[$name]);
        }
    }
}
