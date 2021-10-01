<?php
/**
 * @package     Joomla.API
 * @subpackage  com_menus
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Api\View\Menus;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

/**
 * The menus view
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
		'menutype',
		'title',
		'description',
		'client_id',
		'count_published',
		'count_unpublished',
		'count_trashed',
	];

	/**
	 * The fields to render items in the documents
	 *
	 * @var  array
	 * @since  4.0.0
	 */
	protected $fieldsToRenderList  = [
		'id',
		'asset_id',
		'menutype',
		'title',
		'description',
		'client_id',
	];
}
