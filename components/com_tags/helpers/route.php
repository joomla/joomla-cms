<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\Component\Tags\Site\Helper\RouteHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Tags Component Route Helper.
 *
 * @since  3.1
 *
 * @deprecated  4.3 will be removed in 6.0
 *              Use \Joomla\Component\Tags\Site\Helper\RouteHelper instead
 */
class TagsHelperRoute extends RouteHelper
{
}
