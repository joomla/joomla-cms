<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Webauthn
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Webauthn\Exception;

// Protect from unauthorized access
defined('_JEXEC') or die();

use RuntimeException;

/**
 * Exception indicating that the Joomla application object is not a CMSApplication subclass.
 *
 * @since   4.0.0
 */
class AjaxNonCmsAppException extends RuntimeException
{

}
