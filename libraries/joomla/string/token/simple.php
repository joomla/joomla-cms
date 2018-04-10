<?php
/**
 * @package     Joomla.Platform
 * @subpackage  String
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Simple token type definition.
 *
 * @since  __DEPLOY_VERSION__
 */
class JStringTokenSimple extends JStringTokenDefinition
{
	/**
	 * Is this token simple or the beginning of a block?
	 *
	 * @return  boolean
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function isSimple()
	{
		return true;
	}
}

