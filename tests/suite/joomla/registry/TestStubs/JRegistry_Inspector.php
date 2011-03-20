<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.UnitTest
 */

defined('JPATH_PLATFORM') or die;

/**
 * Inspector classes for the JRegistry package.
 */

/**
 * @package		Joomla.UnitTest
 * @subpackage	Registry
 */
class JRegistryInspector extends JRegistry
{
	public function bindData(& $parent, $data)
	{
		return parent::bindData($parent, $data);
	}
}