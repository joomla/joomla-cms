<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Privacy Controller
 *
 * @since  __DEPLOY_VERSION__
 */
class PrivacyController extends JControllerLegacy
{
	/**
	 * The default view.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $default_view = 'requests';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function display($cachable = false, $urlparams = array())
	{
		JLoader::register('PrivacyHelper', JPATH_ADMINISTRATOR . '/components/com_privacy/helpers/privacy.php');

		// Get the document object.
		$document = JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName   = $this->input->get('view', $this->default_view);
		$vFormat = $document->getType();
		$lName   = $this->input->get('layout', 'default', 'string');

		// Get and render the view.
		if ($view = $this->getView($vName, $vFormat))
		{
			$model = $this->getModel($vName);
			$view->setModel($model, true);

			// For the request view, we need to also push the action logs model into the view
			if ($vName === 'request')
			{
				JLoader::register('ActionlogsHelper', JPATH_ADMINISTRATOR . '/components/com_actionlogs/helpers/actionlogs.php');
				JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_actionlogs/models', 'ActionlogsModel');

				$logsModel = $this->getModel('Actionlogs', 'ActionlogsModel');

				// Set default ordering for the context
				$logsModel->setState('list.fullordering', 'a.log_date DESC');

				// And push the model into the view
				$view->setModel($logsModel, false);
			}

			$view->setLayout($lName);

			// Push document object into the view.
			$view->document = $document;

			// Load the submenu.
			PrivacyHelper::addSubmenu($this->input->get('view', $this->default_view));

			$view->display();
		}

		return $this;
	}
}
