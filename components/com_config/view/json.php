<?php
/**
 * @package     Joomla.Cms
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Prototype admin view.
 *
 * @package     Joomla.Libraries
 * @subpackage  Model
 * @since       3.2
 */
abstract class ConfigViewJson extends ConfigViewHtmlCms
{
	public $state;

	public $data;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Layout
	 *
	 * @return  string
	 */
	public function render()
	{

		$this->data = $this->model->getData();

		return json_encode($this->data);
	}
}