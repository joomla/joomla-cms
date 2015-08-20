<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Model Interface for the Joomla CMS.
 * 
 * @package     Joomla.Libraries
 * @subpackage  Model
 * @since       3.4
 */
interface JModelCmsInterface extends JModel
{
	/**
	 * Gets the name of the given model.
	 *
	 * @return  string  The name of the model
	 *
	 * @since   3.4
	 */
	public function getName();
}
