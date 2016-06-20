<?php
/**
 * @package     Joomla.Platform
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla Platform Base View Class
 *
 * @since  12.1
 */
abstract class JViewBase implements JView
{
	/**
	 * The model object.
	 *
	 * @var    JModel
	 * @since  12.1
	 */
	protected $model;

	/**
	 * Method to instantiate the view.
	 *
	 * @param   JModel  $model  The model object.
	 *
	 * @since  12.1
	 */
	public function __construct(JModel $model)
	{
		// Setup dependencies.
		$this->model = $model;
	}

	/**
	 * Method to escape output.
	 *
	 * @param   string  $output  The output to escape.
	 *
	 * @return  string  The escaped output.
	 *
	 * @see     JView::escape()
	 * @since   12.1
	 */
	public function escape($output)
	{
		return $output;
	}
}
