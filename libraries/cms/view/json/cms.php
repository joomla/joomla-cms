<?php
/**
 * @package     Joomla.Cms
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Prototype JView class.
 *
 * @package     Joomla.Libraries
 * @subpackage  View
 * @since       3.4
 */
abstract class JViewJsonCms extends JViewCms
{
	/**
	 * Retrieves the data array from the default model. Will
	 * automatically deal with the 3 CMS interfaces for single
	 * model items. For any other situations the method will
	 * need to be overwritten
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function getData()
	{
		$model = $this->getModel();

		if ($model instanceof JModelItemInterface)
		{
			return array(
				'item' => $model->getItem()
			);
		}
		elseif ($model instanceof JModelListInterface)
		{
			return array(
				'items' => $model->getItems()
			);
		}

		// We don't know what type of model we have.
		// Just return an empty array.
		return array();
	}

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   3.2
	 */
	public function render()
	{
		return json_encode($this->getData());
	}
}