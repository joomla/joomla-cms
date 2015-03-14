<?php
/**
 * @package     Joomla.Cms
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Prototype admin view.
 *
 * @since  3.2
 */
abstract class ConfigViewCmsJson extends ConfigViewCmsHtml
{
	public $state;

	public $data;

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   3.2
	 */
	public function render()
	{
		$this->data = $this->model->getData();

		return json_encode($this->data);
	}
}
