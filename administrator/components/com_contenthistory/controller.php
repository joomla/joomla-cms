<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Contenthistory Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 * @since       1.5
 */
class ContenthistoryController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean	      $cachable    If true, the view output will be cached
	 * @param   array         $urlparams   An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController   This object to support chaining.
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$view   = $this->input->get('view', 'history');
		$layout = $this->input->get('layout', 'modal');

		parent::display();

		return $this;
	}
}
