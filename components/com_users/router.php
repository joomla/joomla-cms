<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\Router\RouterView;
use Joomla\CMS\Component\Router\RouterViewConfiguration;
use Joomla\CMS\Component\Router\Rules\MenuRules;
use Joomla\CMS\Component\Router\Rules\NomenuRules;
use Joomla\CMS\Component\Router\Rules\StandardRules;
use Joomla\CMS\Menu\AbstractMenu;

/**
 * Routing class from com_users
 *
 * @since  3.2
 */
class UsersRouter extends RouterView
{
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

		parent::__construct($app, $menu);

		$this->attachRule(new MenuRules($this));
		$this->attachRule(new StandardRules($this));
		$this->attachRule(new NomenuRules($this));
	}
}
