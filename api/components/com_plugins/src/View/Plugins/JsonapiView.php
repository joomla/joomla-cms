<?php
/**
 * @package     Joomla.API
 * @subpackage  com_plugins
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Plugins\Api\View\Plugins;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

/**
 * The plugins view
 *
 * @since  4.0.0
 */
class JsonapiView extends BaseApiView
{
	/**
	 * The fields to render item in the documents
	 *
	 * @var  array
	 * @since  4.0.0
	 */
	protected $fieldsToRenderItem = [
		'id',
		'name',
		'type',
		'element',
		'changelogurl',
		'folder',
		'client_id',
		'enabled',
		'access',
		'protected',
		'checked_out',
		'checked_out_time',
		'ordering',
		'state',
	];

	/**
	 * The fields to render items in the documents
	 *
	 * @var  array
	 * @since  4.0.0
	 */
	protected $fieldsToRenderList = [
		'id',
		'name',
		'element',
		'folder',
		'checked_out',
		'checked_out_time',
		'enabled',
		'access',
		'ordering',
		'editor',
		'access_level',
	];

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
		$item->id = $item->extension_id;
		unset($item->extension_id);

		return $item;
	}
}
