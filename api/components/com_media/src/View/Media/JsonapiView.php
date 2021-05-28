<?php
/**
 * @package     Joomla.API
 * @subpackage  com_media
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Api\View\Media;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\Component\Media\Administrator\Provider\ProviderManager;
use Joomla\Component\Media\Api\Helper\AdapterTrait;
use Joomla\Component\Media\Api\Helper\MediaHelper;

/**
 * Media web service view
 *
 * @since  4.0.0
 */
class JsonapiView extends BaseApiView
{
	use AdapterTrait;

	/**
	 * The fields to render item in the documents
	 *
	 * @var  array
	 * @since  4.0.0
	 */
	protected $fieldsToRenderItem = [
		'type',
		'name',
		'path',
		'extension',
		'size',
		'mime_type',
		'width',
		'height',
		'create_date',
		'create_date_formatted',
		'modified_date',
		'modified_date_formatted',
		'thumb_path',
		'adapter',
		'content'
	];

	/**
	 * The fields to render items in the documents
	 *
	 * @var  array
	 * @since  4.0.0
	 */
	protected $fieldsToRenderList = [
		'type',
		'name',
		'path',
		'extension',
		'size',
		'mime_type',
		'width',
		'height',
		'create_date',
		'create_date_formatted',
		'modified_date',
		'modified_date_formatted',
		'thumb_path',
		'adapter',
		'content'
	];

	/**
	 * Holds the available media file adapters.
	 *
	 * @var   ProviderManager
	 * @since  4.0.0
	 */
	private $providerManager = null;

	/**
	 * Prepare item before render.
	 *
	 * @param   object  $item  The model item
	 *
	 * @return  object
	 *
	 * @since   4.0.0
	 */
	protected function prepareItem($item)
	{
		// Media resources have no id.
		$item->id = '0';

		// Transform resource location into url.
		list('adapter' => $adapterName, 'path' => $path) = MediaHelper::adapterNameAndPath($item->path);
		$adapter = $this->getAdapter($adapterName);
		$item->path = $adapter->getUrl($path);

		return $item;
	}
}
