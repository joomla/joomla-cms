<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! Update Controller
 *
 * @since  2.5.4
 */
class JtestreportController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached.
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController  This object to support chaining.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// Set the default view name and format from the Request.
		$vName   = $this->input->get('view', 'default');
		$vFormat = 'html';
		$lName   = $this->input->get('layout', 'default', 'string');

		$view = $this->getView($vName, $vFormat);

		$model = $this->getModel('default');

		// Push the model into the view (as default).
		$view->setModel($model, true);
		$view->setLayout($lName);

		$view->display();

		return $this;
	}
}
