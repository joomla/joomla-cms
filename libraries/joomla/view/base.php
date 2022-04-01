<?php
/**
 * @package     Joomla.Platform
 * @subpackage  View
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla Platform Base View Class
 *
 * @since       3.0.0
 * @deprecated  4.0 Use the default MVC library
 */
abstract class JViewBase implements JView
{
	/**
	 * The model object.
	 *
	 * @var    JModel
	 * @since  3.0.0
	 */
	protected $model;

	/**
	 * Method to instantiate the view.
	 *
	 * @param   JModel  $model  The model object.
	 *
	 * @since  3.0.0
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
	 * @since   3.0.0
	 */
	public function escape($output)
	{
		return $output;
	}
}
