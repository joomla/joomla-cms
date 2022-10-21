<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.logout
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Plugin\CMSPlugin;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Plugin class for logout redirect handling.
 *
 * @since  1.6
 */
class PlgSystemLogout extends CMSPlugin
{
    /**
     * @var    \Joomla\CMS\Application\CMSApplication
     *
     * @since  3.7.3
     */
    protected $app;

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     *
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * @param   object  &$subject  The object to observe -- event dispatcher.
     * @param   object  $config    An optional associative array of configuration settings.
     *
     * @since   1.6
     */
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);

        // If we are on admin don't process.
        if (!$this->app->isClient('site')) {
            return;
        }

        $hash  = ApplicationHelper::getHash('PlgSystemLogout');

        if ($this->app->input->cookie->getString($hash)) {
            // Destroy the cookie.
            $this->app->input->cookie->set(
                $hash,
                '',
                1,
                $this->app->get('cookie_path', '/'),
                $this->app->get('cookie_domain', '')
            );
        }
    }

    /**
     * Method to handle any logout logic and report back to the subject.
     *
     * @param   array  $user     Holds the user data.
     * @param   array  $options  Array holding options (client, ...).
     *
     * @return  boolean  Always returns true.
     *
     * @since   1.6
     */
    public function onUserLogout($user, $options = [])
    {
        if ($this->app->isClient('site')) {
            // Create the cookie.
            $this->app->input->cookie->set(
                ApplicationHelper::getHash('PlgSystemLogout'),
                true,
                time() + 86400,
                $this->app->get('cookie_path', '/'),
                $this->app->get('cookie_domain', ''),
                $this->app->isHttpsForced(),
                true
            );
        }

        return true;
    }
}
