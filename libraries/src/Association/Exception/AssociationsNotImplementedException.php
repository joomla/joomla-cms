<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Association\Exception;

use Joomla\CMS\Categories\CategoryNotFoundExceptionInterface;

/**
 * The component does not support categories.
 *
 * @since  __DEPLOY_VERSION__
 */
class AssociationsNotImplementedException extends \RuntimeException implements CategoryNotFoundExceptionInterface
{
}
