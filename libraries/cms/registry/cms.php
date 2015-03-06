<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\Registry\Registry;

defined('JPATH_PLATFORM') or die;

/**
 * Registry class for Joomla CMS
 * This is a workaround for the JRegistry = Registry thing
 */
class JRegistryCms extends Registry{}
