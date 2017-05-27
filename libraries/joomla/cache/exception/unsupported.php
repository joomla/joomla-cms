<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Cache
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Exception class defining an unsupported cache storage object
 *
 * @since  3.6.3
 */
class JCacheExceptionUnsupported extends RuntimeException implements JCacheException
{
}
