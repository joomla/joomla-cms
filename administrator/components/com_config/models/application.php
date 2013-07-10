<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Model for the global configuration
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @deprecated  4.0
 */

JLog::add('models/ConfigModelApplication is deprecated. Use model/ConfigModelApplication instead.', JLog::WARNING, 'deprecated');
include JPATH_ADMINISTRATOR . '/components/com_config/model/application.php';
