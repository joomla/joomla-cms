<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Plugin\Attribute;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * This attribute allows the plugin listener to handle AJAX requests in the backend of the site,
 * where normally com_ajax is not available when we are not logged in.
 *
 * @since   5.4.4
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class AllowUnauthorizedAdministratorAccess
{
}
