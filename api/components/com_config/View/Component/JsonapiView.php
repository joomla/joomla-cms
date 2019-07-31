<?php
/**
 * @package     Joomla.API
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Api\View\Component;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\CMS\Serializer\JoomlaSerializer;
use Tobscure\JsonApi\Collection;

/**
 * The component view
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
		try
		{
			$component = ComponentHelper::getComponent($this->get('component_name'));

			if ($component === null || !$component->enabled)
			{
				throw new \RuntimeException('Invalid component name', 400);
			}

			$data = $component->getParams()->toObject();
		}
		catch (\Exception $e)
		{
			throw new \RuntimeException('Internal server error', 500, $e);
		}

		$items = array();

		foreach ($data as $key => $value)
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
		$item->id = ExtensionHelper::getExtensionRecord($this->get('component_name'))->extension_id;

		return $item;
	}
}
