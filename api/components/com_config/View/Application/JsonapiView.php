<?php
/**
 * @package     Joomla.API
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Api\View\Application;

defined('_JEXEC') or die;

use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\CMS\Serializer\JoomlaSerializer;
use Tobscure\JsonApi\Collection;
USE Joomla\Component\Config\Administrator\Model\ApplicationModel;

/**
 * The application view
 *
 * @since  4.0.0
 */
class JsonapiView extends BaseApiView
{
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
		/** @var ApplicationModel $model */
		$model = $this->getModel();
		$items = array();

		foreach ($model->getData() as $key => $value)
		{
			$item    = (object) array($key => $value);
			$items[] = $this->prepareItem($item);
		}

		$collection = (new Collection($items, new JoomlaSerializer($this->type)));

		$this->document->setData($collection);

		return $this->document->render();
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
		$item->id = ExtensionHelper::getExtensionRecord('files_joomla')->extension_id;

		return $item;
	}
}
