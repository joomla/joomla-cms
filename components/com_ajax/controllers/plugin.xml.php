<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_ajax
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The AJAX Plugin Controller for XML format
 *
 * The plugin event triggered is onAjaxFoo, where 'foo' is
 * the value of the 'name' variable passed via the URL
 * Example: index.php?option=com_ajax&task=plugin.call&name=foo&format=xml
 *
 * @package     Joomla.Site
 * @subpackage  com_ajax
 *
 * @since   3.2
 */
class AjaxControllerPlugin extends JControllerLegacy
{

	/**
	 * Do job!
	 *
	 */
	public function call()
	{
		// Interaction with "ajax" group
		JPluginHelper::importPlugin('ajax');
		// Allow interaction with "content" group
		JPluginHelper::importPlugin('content');
		// Allow interaction with "system" group
		JPluginHelper::importPlugin('system');

		$plugin     = ucfirst($this->input->get('name'));
		$dispatcher = JEventDispatcher::getInstance();

		// Call the plugins
		$results = $dispatcher->trigger('onAjax' . $plugin );
		$results = implode("\n", $results);

		// Test whether we have result and it is the valid XML
		libxml_use_internal_errors(true);
		if($results
			&& !simplexml_load_string($results)
			&& $error = libxml_get_last_error())
		{
			// Make the error message
			$message = '';
			switch ($error->level) {
				case LIBXML_ERR_WARNING :
					$message .= 'Warning ';
					break;
				case LIBXML_ERR_ERROR :
					$message .= 'Error ';
					break;
				case LIBXML_ERR_FATAL :
					$message .= 'Fatal Error ';
					break;
			}
			$message .= $error->code . ': ' . trim($error->message) . '; Line: ' . $error->line . '; Column: ' . $error->column;
			throw new UnexpectedValueException($message, 500);
		}

		// Output XML string
		echo $results;

		return true;
	}
}
