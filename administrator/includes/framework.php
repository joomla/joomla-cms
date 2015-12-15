<?php
/**
 * @package    Joomla.Administrator
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Joomla system checks.
@ini_set('magic_quotes_runtime', 0);

require_once dirname(dirname(__DIR__)) . '/includes/framework.php';

/**
 * Provides methods to initialize the cms environment and dependencies.
 *
 * @since  VERSION
 */
class JBootstrapAdministrator extends JBootstrap
{
	/**
	 * @var  string  Relative location of installation directory, used for redirection.
	 */
	protected static $install_path = '../installation/index.php';

	/**
	 * Load the cms and application context.
	 *
	 * @return  void
	 */
	public static function loadCms()
	{
		parent::loadCms();

		require_once JPATH_BASE . '/includes/helper.php';
		require_once JPATH_BASE . '/includes/toolbar.php';
	}
}
