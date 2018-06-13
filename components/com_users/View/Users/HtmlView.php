<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Users\Site\View\Users;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Application\ApplicationHelper;

/**
 * Users List view class for Users.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The model state
	 *
	 * @var  \JObject
	 */
	protected $state;

	/**
	 * @var array
	 */
	protected $items;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed   A string if successful, otherwise an Error object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$app        = Factory::getApplication();
		$this->items  = $this->get('Items');

		PluginHelper::importPlugin('content');

		foreach ($this->items as $item)
		{
			$item->slug = $item->id . ":" . ApplicationHelper::stringURLSafe($item->name);

			// Store the events for later
			$item->event = new \stdClass;

			$item->text = '';

			$app->triggerEvent('onContentPrepare', array('com_users.user', &$item, &$item->params, 0));

			$results = $app->triggerEvent('onContentAfterTitle', array('com_users.user', &$item, &$item->params, 0));
			$item->event->afterDisplayTitle = trim(implode("\n", $results));

			$results = $app->triggerEvent('onContentBeforeDisplay', array('com_users.user', &$item, &$item->params, 0));
			$item->event->beforeDisplayContent = trim(implode("\n", $results));

			$results = $app->triggerEvent('onContentAfterDisplay', array('com_users.user', &$item, &$item->params, 0));
			$item->event->afterDisplayContent = trim(implode("\n", $results));
		}

		return parent::display($tpl);
	}

}
