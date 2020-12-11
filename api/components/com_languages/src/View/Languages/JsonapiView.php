<?php
/**
 * @package     Joomla.API
 * @subpackage  com_languages
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Languages\Api\View\Languages;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

/**
 * The languages view
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
		'asset_id',
		'lang_code',
		'title',
		'title_native',
		'sef',
		'image',
		'description',
		'metakey',
		'metadesc',
		'sitename',
		'published',
		'access',
		'ordering',
		'access_level',
		'home',
	];

	/**
	 * The fields to render items in the documents
	 *
	 * @var  array
	 * @since  4.0.0
	 */
	protected $fieldsToRenderList = [
		'id',
		'asset_id',
		'lang_code',
		'title',
		'title_native',
		'sef',
		'image',
		'description',
		'metakey',
		'metadesc',
		'sitename',
		'published',
		'access',
		'ordering',
		'access_level',
		'home',
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
		$item->id = $item->lang_id;
		unset($item->lang->id);

		return parent::prepareItem($item);
	}
}
