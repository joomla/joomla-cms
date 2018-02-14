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

		return $container;
	},
	true
);
