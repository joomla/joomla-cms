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
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Language\Text;

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
	 * @var  \Joomla\Registry\Registry
	 */
	protected $state;

	/**
	 * User items data
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * The group
	 *
	 * @var CMSObject
	 */
	protected $group;

	/**
	 * The page parameters
	 *
	 * @var    \Joomla\Registry\Registry|null
	 * @since  4.0.0
	 */
	protected $params = null;

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
		$app          = Factory::getApplication();
		$this->items  = $this->get('Items');
		$this->state  = $this->get('State');
		$this->params = $this->state->get('params');



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

		$menus   = $app->getMenu();
		$menu = $menus->getActive();

		$this->group = new CMSObject;
		$this->group->title = $menu->title;
		$this->group->id = $this->state->get('user.group');

		if ($menu
			&& $menu->component == 'com_users'
			&& isset($menu->query['view'], $menu->query['id'])
			&& $menu->query['view'] == 'users'
			&& $menu->query['id'] == $this->group->id)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
			$title = $this->params->get('page_title', $menu->title);
		}
		else
		{
			$this->params->def('page_heading', $this->group->title);
			$title = $this->group->title;
			$this->params->set('page_title', $title);
		}

		// Check for empty title and add site name if param is set
		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		if (empty($title))
		{
			$title = $this->group->title;
		}

		$this->document->setTitle($title);

		return parent::display($tpl);
	}

}
