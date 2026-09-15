<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table\Exception;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface that all exceptions thrown from the Table layer should implement, so that
 * calling Model code can catch every Table-layer failure with a single catch block.
 *
 * @since  __DEPLOY_VERSION__
 */
interface TableExceptionInterface extends \Throwable
{
}
