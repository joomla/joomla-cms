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
 * The Plugin Controller
 *
 * Plugin support is based on the "Ajax" plugin group.
 * The plugin event triggered is onAjaxFoo, where 'foo' is
 * the value of the 'name' variable passed via the URL
 * (i.e. index.php?option=com_ajax&task=plugin.call&name=foo)
 *
 * @package     Joomla.Site
 * @subpackage  com_ajax
 */
class AjaxControllerPlugin extends JControllerLegacy
{

	/**
	 * Do job!
	 *
	 */
	public function call()
	{
		JPluginHelper::importPlugin('ajax');
		$plugin     = ucfirst($this->input->get('name'));
		$dispatcher = JEventDispatcher::getInstance();
		$result 	= '';

		try {
			$results = $dispatcher->trigger ( 'onAjax' . $plugin );
		}
		catch ( Exception $e )
		{
			$results = $e;
		}

		// Output exception
		if ($results instanceof Exception)
		{
			// Log an error
			JLog::add($results->getMessage(), JLog::ERROR);

			// Set header
			JResponse::setHeader('status', $results->getCode(), true);

			// Echo exception message
			$result = $results->getMessage();
		}
		// Output string/ null
		elseif (is_scalar($results))
		{
			$result = (string) $results;
		}
		// Output array/ object
		else
		{
			$result = implode("\n", (array) $results);
		}

		echo $result;

		return true;
	}
}
