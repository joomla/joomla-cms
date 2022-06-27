<?php
/**
 * @package     Joomla.API
 * @subpackage  com_media
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Api\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\ApiController;
use Joomla\Component\Media\Administrator\Exception\InvalidPathException;
use Joomla\Component\Media\Administrator\Provider\ProviderManagerHelperTrait;

/**
 * Media web service controller.
 *
 * @since  4.1.0
 */
class AdaptersController extends ApiController
{
	use ProviderManagerHelperTrait;

	/**
	 * The content type of the item.
	 *
	 * @var    string
	 * @since  4.1.0
	 */
	protected $contentType = 'adapters';

	/**
	 * The default view for the display method.
	 *
	 * @var    string
	 *
	 * @since  4.1.0
	 */
	protected $default_view = 'adapters';

	/**
	 * Display one specific adapter.
	 *
	 * @param   string  $path  The path of the file to display. Leave empty if you want to retrieve data from the request.
	 *
	 * @return  static  A \JControllerLegacy object to support chaining.
	 *
	 * @throws  InvalidPathException
	 * @throws  \Exception
	 *
	 * @since   4.1.0
	 */
	public function displayItem($path = '')
	{
		// Set the id as the parent sets it as int
		$this->modelState->set('id', $this->input->get('id', '', 'string'));

		return parent::displayItem();
	}
}
