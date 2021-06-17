<?php
/**
 * @package     Joomla.API
 * @subpackage  com_fields
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Api\View\Fields;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\CMS\Router\Exception\RouteNotFoundException;

/**
 * The fields view
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
		'typeAlias',
		'id',
		'asset_id',
		'context',
		'group_id',
		'title',
		'name',
		'label',
		'default_value',
		'type',
		'note',
		'description',
		'state',
		'required',
		'checked_out',
		'checked_out_time',
		'ordering',
		'params',
		'fieldparams',
		'language',
		'created_time',
		'created_user_id',
		'modified_time',
		'modified_by',
		'access',
		'assigned_cat_ids',
	];

	/**
	 * The fields to render items in the documents
	 *
	 * @var  array
	 * @since  4.0.0
	 */
	protected $fieldsToRenderList = [
		'id',
		'title',
		'name',
		'checked_out',
		'checked_out_time',
		'note',
		'state',
		'access',
		'created_time',
		'created_user_id',
		'ordering',
		'language',
		'fieldparams',
		'params',
		'type',
		'default_value',
		'context',
		'group_id',
		'label',
		'description',
		'required',
		'language_title',
		'language_image',
		'editor',
		'access_level',
		'author_name',
		'group_title',
		'group_access',
		'group_state',
		'group_note',
	];

	/**
	 * Execute and display a template script.
	 *
	 * @param   object  $item  Item
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function displayItem($item = null)
	{
		if ($item === null)
		{
			/** @var \Joomla\CMS\MVC\Model\AdminModel $model */
			$model = $this->getModel();
			$item  = $this->prepareItem($model->getItem());
		}

		if ($item->id === null)
		{
			throw new RouteNotFoundException('Item does not exist');
		}

		if ($item->context != $this->getModel()->getState('filter.context'))
		{
			throw new RouteNotFoundException('Item does not exist');
		}

		return parent::displayItem($item);
	}
}
