<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$container->share(
	'ContentComponentContainer',
	function (\Joomla\DI\Container $parent)
	{
		$container = new \Joomla\CMS\Extension\ComponentContainer($parent);
		$container->set('categories', new \Joomla\Component\Content\Site\Service\Category);
		$container->set(
			'site.dispatcher',
			function ()  use($parent)
			{
				$app = $parent->get(\Joomla\CMS\Application\SiteApplication::class);
				return new \Joomla\Component\Content\Site\Dispatcher\Dispatcher($app, $app->input);
			}
		);
		$container->set(
			'administrator.dispatcher',
			function ()  use($parent)
			{
				$app = $parent->get(\Joomla\CMS\Application\AdministratorApplication::class);
				return new \Joomla\Component\Content\Administrator\Dispatcher\Dispatcher($app, $app->input);
			}
		);

		return $container;
	},
	true
);
