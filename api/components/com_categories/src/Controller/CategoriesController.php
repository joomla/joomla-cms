<?php
/**
 * @package     Joomla.API
 * @subpackage  com_categories
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Categories\Api\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\CMS\Router\Exception\RateLimitException;

/**
 * The categories controller
 *
 * @since  4.0.0
 */
class CategoriesController extends ApiController
{
	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $contentType = 'categories';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $default_view = 'categories';

	/**
	 * Method to allow extended classes to manipulate the data to be saved for an extension.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	protected function preprocessSaveData(array $data): array
	{
		$extension = $this->getExtensionFromInput();
		$data['extension'] = $extension;

		// TODO: This is a hack to drop the extension into the global input object - to satisfy how state is built
		//       we should be able to improve this in the future
		$this->input->set('extension', $extension);

		return $data;
	}

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
		$this->modelState->set('filter.extension', $this->getExtensionFromInput());
		$extension = substr($this->getExtensionFromInput(), 4);

		if ((int) $this->input->get('isPublicApi', 0) === 1)
		{
			$option = $extension . '.webservices.ratelimit';
			$ratelimit = (int) $this->input->get($option);
			$limit     = (int) $this->input->get($extension . '.webservices.x-limit');
			$remaining = (int) $this->input->get($extension . '.webservices.x-remaining');
			$reset     = $this->input->get($extension . '.webservices.x-reset', 'string');
			$xreset    = gmdate('D, d M Y H:i:s \G\M\T', $reset);
			$this->app->setHeader('X-RateLimit-Limit', $limit);
			$this->app->setHeader('X-RateLimit-Remaining', $remaining);
			$this->app->setHeader('X-RateLimit-Reset', $xreset);

			if ($ratelimit > 0)
			{
				throw new RateLimitException;
			}

			$this->modelState->set('filter.published', 1);
			$this->app->triggerEvent('onPublicGet', [$extension . '.webservice']);
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
		$extension = substr($this->getExtensionFromInput(), 4);
		$this->modelState->set('filter.extension', $this->getExtensionFromInput());

		if ((int) $this->input->get('isPublicApi', 0) === 1)
		{
			$option = $extension . '.webservices.ratelimit';
			$ratelimit = (int) $this->input->get($option);
			$limit     = (int) $this->input->get($extension . '.webservices.x-limit');
			$remaining = (int) $this->input->get($extension . '.webservices.x-remaining');
			$reset     = $this->input->get($extension . '.webservices.x-reset', 'string');
			$xreset    = gmdate('D, d M Y H:i:s \G\M\T', $reset);
			$this->app->setHeader('X-RateLimit-Limit', $limit);
			$this->app->setHeader('X-RateLimit-Remaining', $remaining);
			$this->app->setHeader('X-RateLimit-Reset', $xreset);

			if ($ratelimit > 0)
			{
				throw new RateLimitException;
			}

			$this->modelState->set('filter.published', 1, 'INT');
			$this->app->triggerEvent('onPublicGet', [$extension . '.webservice']);
		}

		return parent::displayList();
	}

	/**
	 * Get extension from input
	 *
	 * @return string
	 *
	 * @since 4.0
	 */
	private function getExtensionFromInput()
	{
		return $this->input->exists('extension') ?
			$this->input->get('extension') : $this->input->post->get('extension');
	}
}
