<?php
/**
 * @package    Joomla.Libraries
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

$container = (new \Joomla\DI\Container)
	->registerServiceProvider(new \Joomla\CMS\Service\Provider\Application)
	->registerServiceProvider(new \Joomla\CMS\Service\Provider\Database)
	->registerServiceProvider(new \Joomla\CMS\Service\Provider\Dispatcher)
	->registerServiceProvider(new \Joomla\CMS\Service\Provider\Form)
	->registerServiceProvider(new \Joomla\CMS\Service\Provider\Document)
	->registerServiceProvider(new \Joomla\CMS\Service\Provider\Menu)
	->registerServiceProvider(new \Joomla\CMS\Service\Provider\Session)
	->registerServiceProvider(new \Joomla\CMS\Service\Provider\Toolbar);

return $container;
