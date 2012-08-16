<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Tracks list controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 * @since       1.6
 */
class BannersControllerTracks extends JControllerLegacy
{
	/**
	 * @var		string	The context for persistent state.
	 * @since	1.6
	 */
	protected $context = 'com_banners.tracks';

	/**
	 * Proxy for getModel.
	 *
	 * @param	string	$name	The name of the model.
	 * @param	string	$prefix	The prefix for the model class name.
	 *
	 * @return	JModel
	 * @since	1.6
	 */
	public function getModel($name = 'Tracks', $prefix = 'BannersModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	/**
	 * Display method for the raw track data.
	 *
	 * @param	boolean			If true, the view output will be cached
	 * @param	array			An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 * @todo	This should be done as a view, not here!
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// Get the document object.
		$document	= JFactory::getDocument();
		$vName		= 'tracks';
		$vFormat	= 'raw';

		// Get and render the view.
		if ($view = $this->getView($vName, $vFormat)) {
			// Get the model for the view.
			$model = $this->getModel($vName);

			// Load the filter state.
			$app = JFactory::getApplication();

			$type = $app->getUserState($this->context.'.filter.type');
			$model->setState('filter.type', $type);

			$begin = $app->getUserState($this->context.'.filter.begin');
			$model->setState('filter.begin', $begin);

			$end = $app->getUserState($this->context.'.filter.end');
			$model->setState('filter.end', $end);

			$categoryId = $app->getUserState($this->context.'.filter.category_id');
			$model->setState('filter.category_id', $categoryId);

			$clientId = $app->getUserState($this->context.'.filter.client_id');
			$model->setState('filter.client_id', $clientId);

			$model->setState('list.limit', 0);
			$model->setState('list.start', 0);

			$form = JRequest::getVar('jform');
			$model->setState('basename', $form['basename']);
			$model->setState('compressed', $form['compressed']);

			$config = JFactory::getConfig();
			$cookie_domain = $config->get('cookie_domain', '');
			$cookie_path = $config->get('cookie_path', '/');

			setcookie(JApplication::getHash($this->context.'.basename'), $form['basename'], time() + 365 * 86400, $cookie_path, $cookie_domain);
			setcookie(JApplication::getHash($this->context.'.compressed'), $form['compressed'], time() + 365 * 86400, $cookie_path, $cookie_domain);

			// Push the model into the view (as default).
			$view->setModel($model, true);

			// Push document object into the view.
			$view->document = $document;

			$view->display();
		}
	}
}
