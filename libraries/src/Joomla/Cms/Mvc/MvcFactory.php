<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\Mvc;

defined('JPATH_PLATFORM') or die;


interface MvcFactory
{
	public function createModel($name, $prefix = '', $config = array());

	public function createView($name, $prefix = '', $type = '', $config = array());

	public function createTable($name, $prefix = 'Table', $config = array());
}