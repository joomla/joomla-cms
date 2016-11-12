<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Routing class from com_users
 *
 * @since  3.2
 */
class UsersRouter extends JComponentRouterView
{
	/**
	 * Users Component router constructor
	 *
	 * @param   JApplicationCms  $app   The application object
	 * @param   JMenu            $menu  The menu object to work with
	 */
	public function __construct($app = null, $menu = null)
	{
		$this->registerView(new JComponentRouterViewconfiguration('login'));
		$profile = new JComponentRouterViewconfiguration('profile');
		$profile->addLayout('edit');
		$this->registerView($profile);
		$this->registerView(new JComponentRouterViewconfiguration('registration'));
		$this->registerView(new JComponentRouterViewconfiguration('remind'));
		$this->registerView(new JComponentRouterViewconfiguration('reset'));

		parent::__construct($app, $menu);

		$this->attachRule(new JComponentRouterRulesMenu($this));
		$this->attachRule(new JComponentRouterRulesStandard($this));
		$this->attachRule(new JComponentRouterRulesNomenu($this));
	}
}
