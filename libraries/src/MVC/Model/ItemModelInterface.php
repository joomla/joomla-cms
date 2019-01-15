<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model;

defined('JPATH_PLATFORM') or die;

/**
 * Interface for an item model.
 *
 * @since  __DEPLOY_VERSION__
 */
interface ItemModelInterface
{
	/**
	 * Method to get an item.
	 *
	 * @param   integer  $pk  The id of the item
	 *
	 * @return  object
	 *
	 * @since __DEPLOY_VERSION__
	 * @throws \Exception
	 */
	public function getItem($pk = null);
}
