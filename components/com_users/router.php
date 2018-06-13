<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Menu\AbstractMenu;

/**
 * Routing class from com_users
 *
 * @since  3.2
 */
class UsersRouter extends RouterView
{
	protected $noIDs = false;

	/**
	 * Users Component router constructor
	 *
	 * @param   CMSApplication  $app   The application object
	 * @param   AbstractMenu    $menu  The menu object to work with
	 */
	public function __construct($app = null, $menu = null)
	{
		$this->registerView(new RouterViewConfiguration('login'));
		$profile = new RouterViewConfiguration('profile');
		$profile->addLayout('edit');
		$this->registerView($profile);
		$this->registerView(new RouterViewConfiguration('registration'));
		$this->registerView(new RouterViewConfiguration('remind'));
		$this->registerView(new RouterViewConfiguration('reset'));

		$users = new RouterViewConfiguration('users');
		$users->setKey('id');
		$this->registerView($users);

		$user = new RouterViewConfiguration('user');
		$user->setKey('id')->setParent($users, 'groupId');
		$this->registerView($user);

		parent::__construct($app, $menu);

		$this->attachRule(new MenuRules($this));
		$this->attachRule(new StandardRules($this));
		$this->attachRule(new NomenuRules($this));
	}

	/**
	 * Method to get the segment(s) for a category
	 *
	 * @param   string  $id     ID of the category to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 *
	 * @return  array|string  The segments of this item
	 */
	public function getUsersSegment($id, $query)
	{
		return array((int) $id => $id);
	}

	/**
	 * Method to get the segment(s) for an user
	 *
	 * @param   string  $id     ID of the user to retrieve the segments for
	 * @param   array   $query  The request that is built right now
	 *
	 * @return  array|string  The segments of this item
	 */
	public function getUserSegment($id, $query)
	{
		if ($this->noIDs)
		{
			list($void, $segment) = explode(':', $id, 2);

			return array($void => $segment);
		}

		return array((int) $id => $id);
	}

	/**
	 * Method to get the segment(s) for an user
	 *
	 * @param   string  $segment  Segment of the user to retrieve the ID for
	 * @param   array   $query    The request that is parsed right now
	 *
	 * @return  mixed   The id of this item or false
	 */
	public function getUserId($segment, $query)
	{

		if ($this->noIDs)
		{
			list($id, $segmentList) = explode('-', $segment, 2);

			return (int) $id;
		}

		return (int) $segment;

	}
}
