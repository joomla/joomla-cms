<?php
/**
 * @package     Joomla.API
 * @subpackage  com_users
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Api\View\Users;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\CMS\Router\Exception\RouteNotFoundException;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

/**
 * The users view
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
		'groups',
		'name',
		'username',
		'email',
		'registerDate',
		'lastvisitDate',
		'lastResetTime',
		'resetCount',
		'sendEmail',
		'block',
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
		'username',
		'email',
		'group_count',
		'group_names',
		'registerDate',
		'lastvisitDate',
		'lastResetTime',
		'resetCount',
		'sendEmail',
		'block',
	];

	/**
	 * Execute and display a template script.
	 *
	 * @param   array|null  $items  Array of items
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function displayList(array $items = null)
	{
		foreach (FieldsHelper::getFields('com_users.user') as $field)
		{
			$this->fieldsToRenderList[] = $field->name;
		}

		return parent::displayList();
	}

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
		foreach (FieldsHelper::getFields('com_users.user') as $field)
		{
			$this->fieldsToRenderItem[] = $field->name;
		}

		return parent::displayItem();
	}

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
		if (empty($item->username))
		{
			throw new RouteNotFoundException('Item does not exist');
		}

		foreach (FieldsHelper::getFields('com_users.user', $item, true) as $field)
		{
			$item->{$field->name} = $field->apivalue ?? $field->rawvalue;
		}

		return parent::prepareItem($item);
	}
}
