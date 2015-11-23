<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumViewLeaderboard extends JViewLegacy
{
	protected $extension = 'com_cjforum';
	protected $defaultPageTitle = 'COM_CJFORUM_LEADERBOARD';
	protected $viewName = 'users';

	/**
	 * Execute and display a template script.
	 *
	 * @param string $tpl
	 *        	The name of the template file to parse; automatically searches
	 *        	through the template paths.
	 *        	
	 * @return mixed A string if successful, otherwise a Error object.
	 */
	public function display ($tpl = null)
	{
		$app    = JFactory::getApplication();
		$user   = JFactory::getUser();
		$params = $app->getParams();
		$userIds = array();
		
		// Get some data from the models
		$state      = $this->get('State');
		$items      = $this->get('Items');
		$pagination = $this->get('Pagination');
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
		
			return false;
		}
		
		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));
		$this->state      = &$state;
		$this->items      = &$items;
		$this->params     = &$params;
		$this->pagination = &$pagination;
		$this->user       = &$user;
		$this->heading	  = JText::_('COM_CJFORUM_LEADERBOARD');
		
		// Check for layout override only if this is not the active menu item
		// If it is the active menu item, then the view and category id will match
		$active = $app->getMenu()->getActive();
		
		if (isset($active->query['layout']))
		{
			// We need to set the layout in case this is an alternative menu item (with an alternative layout)
			$this->setLayout($active->query['layout']);
		}
		
		// Compute the topic slugs and prepare introtext (runs content plugins).
		foreach ($this->items as $item)
		{
			$userIds[] = $item->id;
		}
		
		if(!empty($userIds))
		{
			$api = new CjLibApi();
			$avatar = $params->get('user_avatar', 'cjforum');
			$profile = $params->get('avatar_component', 'cjforum');
			
			$api->prefetchUserProfiles($avatar, $userIds);
			
			if($avatar != $profile)
			{
				$api->prefetchUserProfiles($profile, $userIds);
			}
		}
		
		return parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return void
	 */
	protected function prepareDocument ()
	{
		$app = JFactory::getApplication();
		$menu = $app->getMenu()->getActive();
		
		$id = (int) @$menu->query['id'];
		
		parent::addFeed();
	}
}
