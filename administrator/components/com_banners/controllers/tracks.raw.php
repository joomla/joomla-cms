<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Tracks list controller class.
 *
 * @since  1.6
 */
class BannersControllerTracks extends JControllerLegacy
{
	/**
	 * The context for persistent state.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $context = 'com_banners.tracks';

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The name of the model.
	 * @param   string  $prefix  The prefix for the model class name.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JModelLegacy
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Tracks', $prefix = 'BannersModel', $config = array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	/**
	 * Display method for the raw track data.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  BannersControllerTracks  This object to support chaining.
	 *
	 * @since   1.5
	 * @todo    This should be done as a view, not here!
	 */
	public function display($cachable = false, $urlparams = array())
	{
		// Get the document object.
		$vName = 'tracks';

		// Get and render the view.
		if ($view = $this->getView($vName, 'raw'))
		{
			// Get the model for the view.
			/** @var BannersModelTracks $model */
			$model = $this->getModel($vName);

			// Load the filter state.
			$app = JFactory::getApplication();

			$model->setState('filter.type', $app->getUserState($this->context . '.filter.type'));
			$model->setState('filter.begin', $app->getUserState($this->context . '.filter.begin'));
			$model->setState('filter.end', $app->getUserState($this->context . '.filter.end'));
			$model->setState('filter.category_id', $app->getUserState($this->context . '.filter.category_id'));
			$model->setState('filter.client_id', $app->getUserState($this->context . '.filter.client_id'));
			$model->setState('list.limit', 0);
			$model->setState('list.start', 0);

			$form = $this->input->get('jform', array(), 'array');

			$model->setState('basename', $form['basename']);
			$model->setState('compressed', $form['compressed']);

			// Create one year cookies.
			$cookieLifeTime = time() + 365 * 86400;
			$cookieDomain   = $app->get('cookie_domain', '');
			$cookiePath     = $app->get('cookie_path', '/');
			$isHttpsForced  = $app->isHttpsForced();

			$app->input->cookie->set(
				JApplicationHelper::getHash($this->context . '.basename'),
				$form['basename'],
				$cookieLifeTime,
				$cookiePath,
				$cookieDomain,
				$isHttpsForced,
				true
			);

			$app->input->cookie->set(
				JApplicationHelper::getHash($this->context . '.compressed'),
				$form['compressed'],
				$cookieLifeTime,
				$cookiePath,
				$cookieDomain,
				$isHttpsForced,
				true
			);

			// Push the model into the view (as default).
			$view->setModel($model, true);

			// Push document object into the view.
			$view->document = JFactory::getDocument();

			$view->display();
		}

		return $this;
	}
}
