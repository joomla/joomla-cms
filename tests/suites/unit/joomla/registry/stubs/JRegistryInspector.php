<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Registry
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Inspector classes for the JRegistry package.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Registry
 * @since       11.1
 */
class JRegistryInspector extends JRegistry
{
	/**
	 * Test...
	 *
	 * @param   object  &$parent  @todo
	 * @param   mixed   $data     @todo
	 *
	 * @return void
	 */
	public function bindData(& $parent, $data)
	{
		return parent::bindData($parent, $data);
	}
}
