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
	'ContentContainer',
	function (\Joomla\DI\Container $parent)
	{
		$container = new \Joomla\CMS\Component\ComponentContainer($parent);
		$container->registerServiceProvider(
			new \Joomla\CMS\Component\Service\Provider\Categories(['table' => '#__content', 'extension' => 'com_content'])
		);

		return $container;
	},
	true
);
