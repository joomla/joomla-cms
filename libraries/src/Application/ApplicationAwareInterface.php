<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Application;

/**
 * Interface defining an object aware of the global application object
 *
 * @since  __DEPLOY_VERSION__
 */
interface ApplicationAwareInterface
{
    /**
     * Sets the application to use.
     *
     * @param   CMSApplicationInterface  $application  The application
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function setApplication(CMSApplicationInterface $application): void;
}
