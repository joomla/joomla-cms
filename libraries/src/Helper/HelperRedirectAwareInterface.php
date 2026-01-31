<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Table\Table;

/**
 * Interface for classes that are aware of automated redirects.
 *
 * @since  __DEPLOY_VERSION__
 */
interface HelperRedirectAwareInterface
{
    /**
     * Returns a link for a given table object or null
     *
     * @param Table $table
     *
     * @return string|null
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getLinkForRedirect(Table $table): ?string;
}
