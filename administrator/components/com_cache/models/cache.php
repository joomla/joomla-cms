<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Cache Model
 *
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 * @since       1.6
 * @deprecated  4.0
 */

JLog::add('models/CacheModelCache is deprecated. Use model/CacheModelCache instead.', JLog::WARNING, 'deprecated');
include_once JPATH_ADMINISTRATOR . '/components/com_cache/model/cache.php';
