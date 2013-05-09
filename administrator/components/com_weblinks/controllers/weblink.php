<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Weblink controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 * @since       1.6
 */
class WeblinksControllerWeblink extends JControllerForm
{
	/*
	 * @var    string Model name
	 * @since  3.1
	 */
	protected $modelName = 'Weblink';

	/**
	 * @var    string  The URL option for the component.
	 * @since  3.1
	 */
	protected $option = 'com_weblinks';

	/*
	 * @var  $redirectUrl  Url for redirection after featuring
	*/
	protected $redirectUrl = 'index.php?option=com_weblinks&view=weblinks';

	/**
	 * Function that allows child controller access to model data after the data has been saved.
	 *
	 * @param   JModelLegacy  $model      The data model object.
	 * @param   array         $validData  The validated data.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function postSaveHook(JModelLegacy $model, $validData = array())
	{
		if ($task == 'save')
		{
			$this->setRedirect(JRoute::_($redirectUrl, false));
		}
	}
}
