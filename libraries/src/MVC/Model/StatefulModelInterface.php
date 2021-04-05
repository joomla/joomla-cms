<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model;

\defined('JPATH_PLATFORM') or die;

/**
 * Interface for a stateful model.
 *
 * @since  4.0.0
 */
interface StatefulModelInterface
{
	/**
	 * Method to get model state variables.
	 *
	 * @param   string  $property  Optional parameter name
	 * @param   mixed   $default   Optional default value
	 *
	 * @return  mixed  The property where specified, the state object where omitted
	 *
	 * @since   4.0.0
	 */
	public function getState($property = null, $default = null);

	/**
	 * Method to set model state variables.
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $value     The value of the property to set or null.
	 *
	 * @return  mixed  The previous value of the property or null if not set.
	 *
	 * @since   4.0.0
	 */
	public function setState($property, $value = null);
}
