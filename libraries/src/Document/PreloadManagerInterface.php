<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document;

use Joomla\Preload\PreloadManagerInterface as FrameworkPreloadManagerInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Preload Manager Interface
 *
 * @since  4.0.0
 * @deprecated  __DEPLOY_VERSION__ will be removed in 8.0
 *              Use Joomla\Preload\PreloadManagerInterface directly
 */
interface PreloadManagerInterface extends FrameworkPreloadManagerInterface
{
}
