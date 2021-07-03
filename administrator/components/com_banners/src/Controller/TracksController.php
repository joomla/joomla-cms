<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Tracks list controller class.
 *
 * @since  1.6
 */
class TracksController extends BaseController
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $context = 'com_banners.tracks';

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel  The model.
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Tracks', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Method to remove a record.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function delete()
	{
		// Check for request forgeries.
		$this->checkToken();

		// Get the model.
		/** @var \Joomla\Component\Banners\Administrator\Model\TracksModel $model */
		$model = $this->getModel();

		// Load the filter state.
		$model->setState('filter.type', $this->app->getUserState($this->context . '.filter.type'));
		$model->setState('filter.begin', $this->app->getUserState($this->context . '.filter.begin'));
		$model->setState('filter.end', $this->app->getUserState($this->context . '.filter.end'));
		$model->setState('filter.category_id', $this->app->getUserState($this->context . '.filter.category_id'));
		$model->setState('filter.client_id', $this->app->getUserState($this->context . '.filter.client_id'));
		$model->setState('list.limit', 0);
		$model->setState('list.start', 0);

		$count = $model->getTotal();

		// Remove the items.
		if (!$model->delete())
		{
			$this->app->enqueueMessage($model->getError(), 'warning');
		}
		elseif ($count > 0)
		{
			$this->setMessage(Text::plural('COM_BANNERS_TRACKS_N_ITEMS_DELETED', $count));
		}
		else
		{
			$this->setMessage(Text::_('COM_BANNERS_TRACKS_NO_ITEMS_DELETED'));
		}

		$this->setRedirect('index.php?option=com_banners&view=tracks');
	}

	/**
	 * Display method for the raw track data.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
	 *
	 * @return  static  This object to support chaining.
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
			// Check for request forgeries.
			$this->checkToken('GET');

			// Get the model for the view.
			/** @var \Joomla\Component\Banners\Administrator\Model\TracksModel $model */
			$model = $this->getModel($vName);

			// Load the filter state.
			$app = $this->app;

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

			$this->input->cookie->set(
				ApplicationHelper::getHash($this->context . '.basename'),
				$form['basename'],
				$cookieLifeTime,
				$cookiePath,
				$cookieDomain,
				$isHttpsForced,
				true
			);

			$this->input->cookie->set(
				ApplicationHelper::getHash($this->context . '.compressed'),
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
			$view->document = $this->app->getDocument();

			$view->display();
		}

		return $this;
	}
}
