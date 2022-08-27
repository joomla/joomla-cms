<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\Component\Contact\Site\Helper\RouteHelper;

/**
 * Contact Component Route Helper
 *
 * @static
 * @package     Joomla.Site
 * @subpackage  com_contact
 * @since       1.5
 *
 * #deprecated  4.3 will be removed in 6.0
 *              Use \Joomla\Component\Contact\Site\Helper\RouteHelper instead
 *              Example: RouteHelper::method();
 */
abstract class ContactHelperRoute extends RouteHelper
{
}
