<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.model');
jimport('joomla.database.query');

/**
 * Prototype form model.
 *
 * @package		Joomla.Framework
 * @subpackage	Application
 * @version		1.6
 */
class JModelForm extends JModel
{
	/**
	 * Array of form objects.
	 *
	 * @access	protected
	 * @since	1.1
	 */
	var $_forms = array();

	/**
	 * Method to get a form object.
	 *
	 * @access	public
	 * @param	string		$xml		The form data. Can be XML string if file flag is set to false.
	 * @param	array		$options	Optional array of parameters.
	 * @param	boolean		$clear		Optional argument to force load a new form.
	 * @return	object		JForm object on success, JException on error.
	 * @since	1.1
	 */
	function &getForm($xml, $options = array(), $clear = false)
	{
		// Handle the optional arguments.
		$options['array']	= array_key_exists('array', $options) ? $options['array'] : false;
		$options['file']	= array_key_exists('file', $options) ? $options['file'] : true;
		$options['event']	= array_key_exists('event', $options) ? $options['event'] : null;
		$options['group']	= array_key_exists('group', $options) ? $options['group'] : null;

		// Create a signature hash.
		$hash = md5($xml.serialize($options));

		// Check if we can use a previously loaded form.
		if (isset($this->_forms[$hash]) && !$clear) {
			return $this->_forms[$hash];
		}

		// Get the form.
		jimport('joomla.form.form');
		JForm::addFormPath(JPATH_COMPONENT.DS.'models'.DS.'forms');
		$form = &JForm::getInstance('jform', $xml, $options['file'], $options);

		// Check for an error.
		if (JError::isError($form)) {
			return $form;
		}

		// Look for an event to fire.
		if ($options['event'] !== null)
		{
			// Get the dispatcher.
			$dispatcher	= &JDispatcher::getInstance();

			// Load an optional plugin group.
			if ($options['group'] !== null) {
				JPluginHelper::importPlugin($options['group']);
			}

			// Trigger the form preparation event.
			$results = $dispatcher->trigger($options['event'], array(&$form));

			// Check for errors encountered while preparing the form.
			if (count($results) && in_array(false, $results, true))
			{
				// Get the last error.
				$error = $dispatcher->getError();

				// Convert to a JException if necessary.
				if (!JError::isError($error)) {
					$error = new JException($error, 500);
				}

				return $error;
			}
		}

		// Store the form for later.
		$this->_forms[$hash] = $form;

		return $form;
	}

	/**
	 * Method to validate the form data.
	 *
	 * @access	public
	 * @param	object		$form		The form to validate against.
	 * @param	array		$data		The data to validate.
	 * @return	mixed		Array of filtered data if valid, false otherwise.
	 * @since	1.1
	 */
	function validate($form, $data)
	{
		// Filter and validate the form data.
		$data	= $form->filter($data);
		$return	= $form->validate($data);

		// Check for an error.
		if (JError::isError($return)) {
			$this->setError($return->getMessage());
			return false;
		}

		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message) {
				$this->setError($message);
			}

			return false;
		}

		return $data;
	}
}