<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Categories\Exception;

use Joomla\CMS\Categories\CategoryNotFoundExceptionInterface;

/**
 * The section has not been implemented by the component service provider.
 *
 * @since  __DEPLOY_VERSION__
 */
class SectionNotFoundException extends \RuntimeException implements CategoryNotFoundExceptionInterface
{
}
