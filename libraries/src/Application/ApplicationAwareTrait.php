<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Application;

/**
 * Defines the trait for an Application Aware Class.
 *
 * @since   __DEPLOY_VERSION__
 */
trait ApplicationAwareTrait
{
    /**
     * The application object
     *
     * @var    CMSApplicationInterface
     *
     * @since  __DEPLOY_VERSION__
     */
    private $application;

    /**
     * Sets the application to use.
     *
     * @param   CMSApplicationInterface  $application  The application
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function setApplication(CMSApplicationInterface $application): void
    {
        $this->application = $application;
    }

    /**
     * Returns the internal application or null when not set.
     *
     * @return  CMSApplicationInterface|null
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getApplication(): ?CMSApplicationInterface
    {
        return $this->application;
    }
}
