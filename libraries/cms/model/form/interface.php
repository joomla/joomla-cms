<?php
/**
 * @package     Joomla.Model
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Form Interface for use in JModel for the Joomla CMS.
 *
 * @since  3.4
 */
interface JModelFormInterface extends JModelItemInterface
{
	/**
	 * Method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm  JForm object
	 *
	 * @since   3.2
	 * @throws  RuntimeException
	 */
	public function getForm($data = array(), $loadData = true);
}
