<?php
/**
 * @package     Joomla.API
 * @subpackage  com_modules
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Api\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\CMS\Router\Exception\RateLimitException;
use Joomla\Component\Modules\Administrator\Model\SelectModel;
use Joomla\Component\Modules\Api\View\Modules\JsonapiView;

/**
 * The modules controller
 *
 * @since  4.0.0
 */
class ModulesController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType = 'modules';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $default_view = 'modules';

	/**
	 * Basic display of an item view
	 *
	 * @param   integer  $id  The primary key to display. Leave empty if you want to retrieve data from the request
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function displayItem($id = null)
	{
		$this->modelState->set('filter.client_id', $this->getClientIdFromInput());

		if ((int) $this->input->get('isPublicApi', 0) === 1)
		{
			$this->modelState->set('filter.state', 1);
		}

		return parent::displayItem($id);
	}

	/**
	 * Basic display of a list view
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function displayList()
	{
		if ((int) $this->input->get('isPublicApi', 0) === 1)
		{
			$option    = 'modules.webservices.ratelimit';
			$ratelimit = (int) $this->input->get('modules.webservices.ratelimit');
			$limit     = (int) $this->input->get('modules.webservices.x-limit');
			$remaining = (int) $this->input->get('modules.webservices.x-remaining');
			$reset     = $this->input->get('modules.webservices.x-reset', 'string');
			$xreset    = gmdate('D, d M Y H:i:s \G\M\T', $reset);
			$this->app->setHeader('X-RateLimit-Limit', $limit);
			$this->app->setHeader('X-RateLimit-Remaining', $remaining);
			$this->app->setHeader('X-RateLimit-Reset', $xreset);

			if ($ratelimit > 0)
			{
				throw new RateLimitException;
			}

			$this->app->triggerEvent('onPublicGet', ['modules.webservice']);
			$this->modelState->set('filter.state', 1);
		}

		$this->modelState->set('filter.client_id', $this->getClientIdFromInput());

		return parent::displayList();
	}

	/**
	 * Return module items types
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @since   4.0.0
	 */
	public function getTypes()
	{
		$viewType   = $this->app->getDocument()->getType();
		$viewName   = $this->input->get('view', $this->default_view);
		$viewLayout = $this->input->get('layout', 'default', 'string');

		try
		{
			/** @var JsonapiView $view */
			$view = $this->getView(
				$viewName,
				$viewType,
				'',
				['base_path' => $this->basePath, 'layout' => $viewLayout, 'contentType' => $this->contentType]
			);
		}
		catch (\Exception $e)
		{
			throw new \RuntimeException($e->getMessage());
		}

		/** @var SelectModel $model */
		$model = $this->getModel('select', '', ['ignore_request' => true]);

		if (!$model)
		{
			throw new \RuntimeException(Text::_('JLIB_APPLICATION_ERROR_MODEL_CREATE'));
		}

		$model->setState('client_id', $this->getClientIdFromInput());

		$view->setModel($model, true);

		$view->document = $this->app->getDocument();

		$view->displayListTypes();

		return $this;
	}

	/**
	 * Get client id from input
	 *
	 * @return string
	 *
	 * @since 4.0
	 */
	private function getClientIdFromInput()
	{
		return $this->input->exists('client_id') ?
			$this->input->get('client_id') : $this->input->post->get('client_id');
	}
}
