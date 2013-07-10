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
 * Model for component configuration
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @since       1.5
 * @deprecated  4.0
 */

JLog::add('models/ConfigModelComponent is deprecated. Use model/ConfigModelComponent instead.', JLog::WARNING, 'deprecated');
include JPATH_ADMINISTRATOR . '/components/com_config/model/component.php';
