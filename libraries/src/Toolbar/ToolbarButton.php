<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Layout\FileLayout;

/**
 * Button base class
 *
 * The JButton is the base class for all JButton types
 *
 * @since  3.0
 */
abstract class ToolbarButton
{
	/**
	 * element name
	 *
	 * This has to be set in the final renderer classes.
	 *
	 * @var    string
	 */
	protected $_name = null;

	/**
	 * reference to the object that instantiated the element
	 *
	 * @var    \JButton
	 */
	protected $_parent = null;

	/**
	 * Constructor
	 *
	 * @param   object  $parent  The parent
	 */
	public function __construct($parent = null)
	{
		$this->_parent = $parent;
	}

	/**
	 * Get the element name
	 *
	 * @return  string   type of the parameter
	 *
	 * @since   3.0
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * Get the HTML to render the button
	 *
	 * @param   array  &$definition  Parameters to be passed
	 *
	 * @return  string
	 *
	 * @since   3.0
	 */
	public function render(&$definition)
	{
		/*
		 * Initialise some variables
		 */
		$id = call_user_func_array(array(&$this, 'fetchId'), $definition);
		$action = call_user_func_array(array(&$this, 'fetchButton'), $definition);

		// Build id attribute
		if ($id)
		{
			$id = ' id="' . $id . '"';
		}

		// Build the HTML Button
		$options = array();
		$options['id'] = $id;
		$options['action'] = $action;

		$layout = new FileLayout('joomla.toolbar.base');

		return $layout->render($options);
	}

	/**
	 * Method to get the CSS class name for an icon identifier
	 *
	 * Can be redefined in the final class
	 *
	 * @param   string  $identifier  Icon identification string
	 *
	 * @return  string  CSS class name
	 *
	 * @since   3.0
	 */
	public function fetchIconClass($identifier)
	{
		// It's an ugly hack, but this allows templates to define the icon classes for the toolbar
		$layout = new FileLayout('joomla.toolbar.iconclass');

		return $layout->render(array('icon' => $identifier));
	}

	/**
	 * Get the button
	 *
	 * Defined in the final button class
	 *
	 * @return  string
	 *
	 * @since   3.0
	 */
	abstract public function fetchButton();
}
