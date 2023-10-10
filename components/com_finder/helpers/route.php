<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\Component\Finder\Site\Helper\RouteHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Finder route helper class.
 *
 * @since  2.5
 *
 * @deprecated  4.3 will be removed in 6.0
 *              Use \Joomla\Component\Finder\Site\Helper\RouteHelper instead
 */
class FinderHelperRoute extends RouteHelper
{
}
